<?php

namespace Francerz\GithubWebhook;

use Francerz\Http\BodyParsers;
use Francerz\Http\Helpers\MessageHelper;
use Francerz\Http\Parsers\JsonParser;
use Francerz\Http\Response;
use Francerz\Http\Server;
use Francerz\Http\ServerRequest;
use Francerz\Http\StatusCodes;
use Francerz\Http\StringStream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Handler
{
    private $config;
    private $repos;

    public function loadConfigFromFile(string $path)
    {
        $contents = file_get_contents($path);
        $config = json_decode($contents);

        $this->config = $config;

        $this->repos = array_column(
            array_map(function($v) {
                    $v->name_branch = "{$v->branch}@{$v->name}";
                    return $v;
                },
                $config->repositories
            ),
            null,
            'name_branch'
        );
    }
    public function handle()
    {
        $response = $this->handleRequest(new ServerRequest());
        Server::output($response);
    }
    public function handleRequest(RequestInterface $request) : ResponseInterface
    {
        $response = new Response();
        BodyParsers::register(JsonParser::class);
        $content = MessageHelper::getContent($request);

        if (!is_object($content) || !is_object($content->repository)) {
            ob_start();
            var_dump($content);
            $data = ob_get_clean();
            return $response
                ->withStatus(StatusCodes::BAD_REQUEST)
                ->withBody(new StringStream("Bad request body or repository data.\n".$data));
        }


        $repo_name = $content->repository->full_name;
        $repo_branch = explode('/', $content->ref)[2];
        $repo_key = "{$repo_branch}@{$repo_name}";
        if (!array_key_exists($repo_key, $this->repos)) {
            return $response
                ->withStatus(StatusCodes::BAD_REQUEST)
                ->withBody(new StringStream("Not existant repository {$repo_key}."));
        }
        $repo = $this->repos[$repo_key];

        $event = $request->getHeaderLine('X-GitHub-Event');
        $event_obj = null;
        if (property_exists($repo->events, $event)) {
            $event_obj = $repo->events->{$event};
        } elseif (property_exists($repo->events, '*')) {
            $event_obj = $repo->events->{"*"};
        }
        
        if (is_null($event_obj)) {
            return $response->withStatus(StatusCodes::NO_CONTENT);
        }

        if (!file_exists($repo->path) || !is_dir($repo->path)) {
            return $response
                ->withStatus(StatusCodes::INTERNAL_SERVER_ERROR)
                ->withBody(new StringStream("No repository directory found."));
        }

        chdir($repo->path);
        $commands = $event_obj->commands;
        if (!empty($event_obj->autostash)) {
            $commands = array_merge(
                [
                    'git stash push "'.join('" "', $event_obj->autostash).'"',
                    'git reset'
                ],
                $commands,
                [
                    'git stash pop'
                ]
            );
        }
        if (!empty($commands)) {
            foreach ($commands as $cmd) {
                exec($cmd, $output, $ret);
                if ($ret > 0) {
                    return $response
                        ->withStatus(StatusCodes::INTERNAL_SERVER_ERROR)
                        ->withBody(new StringStream(join("\n", $output)));
                }
            }
        }

        return $response->withStatus(StatusCodes::OK);
    }
}