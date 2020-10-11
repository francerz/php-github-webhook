php-github-webhook
===========================================

Adaptable Github Webhook handler for PHP.

Features:
* Easy config setting up and configuration using a `config.json` file.
* Multiple repositories support, with multiple branches and paths.
* Independant event handling.
* Support to commands previous and after pulling.
* Public file to access internal code preventing direct code access.

Requirements
-----------------------------------------
Basic software is needed:
- Git
- Web Server (tested with Apache 2.4)
- PHP 7 and over

If you're updating private repositories you might need github credentials
It's highly recommended to use an SSH method.

### With Ubuntu 20.04

Creating an SSH directory for www-data to git@github.com
```bash
sudo mkdir /var/www/.ssh
sudo chown -R www-data:www-data /var/www/.ssh
sudo chmod -R 0700 /var/www/.ssh
```

Log in with www-data to generate and install ssh keys
```bash
sudo -su www-data
ssh-keygen -t rsa -b 4096 -C "email@example.com"
```
Name the key as `/var/www/.ssh/github`.
You might want to use a keyphrase that requires aditional settings not covered in this guide.

Open the file `/var/www/.ssh/github.pub` and copy its contents.
Go to https://github.com/settings/ssh/new and paste name the key and paste the content there.

Create a `~/.ssh/config` to default loading key when server starts.
```bash
touch ~/.ssh/config
chmod 600 ~/.ssh/config
nano ~/.ssh/config
```

Put the following in the created file
```text
Host github.com
    User git
    IdentityFile ~/.ssh/github
```

Test the connection
```bash
ssh -T git@github.com
```

Clone the private repository
```bash
git clone git@github.com:{user}/{repo} /path/to/local/repo
```

Or change current repository remote location
```bash
git remote set-url origin git@github.com:{user}/{repo}
```

Test by pulling the latest version from remote repository
```bash
cd /path/to/local/repo
git pull
```


Installation
-----------------------------------------

Clone repository
```bash
git clone https://github.com/francerz/php-github-webhook /path/to/php-github-webhook
```

Install the composer dependencies
```bash
cd /path/to/php-github-webhook
composer install
```

Add Alias to `public` directory
```conf
Alias /.github /path/to/php-github-webhook/public
```

Setup `config.json`
```bash
cp /path/to/php-github-webhook/config.json.dist /path/to/php-github-webhook/config.json
nano /path/to/php-github-webhook/config.json
```
Set the repositories by full_name, branch, path and action events.

Add Webhook to Github Repository by going to:
{repository} > Settings > Webhooks > Add webhook

Capture your credentials, then fill the form:
- Payload URL: `https://my.site.com/.github/hook.php`
- Content-Type: application/json
- Secret: {any key you need, prefer a single use random string}
- Select the events you want to handle.
- Press button Add Webhook.

THAT'S IT!!!
