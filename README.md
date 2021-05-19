# Wordpress For A 2 Sided Market
Kits for a Lockdown

I got this idea from something my daughter and grandson did with our [cultural activity](https://www.campkintail.ca/easter-in-a-basket/). Around the same time my wife did a zoom cookie decorating kit also with our cultural activity. I was reminded of a student in my user experience design class at Laurier that liked to decorate cakes and this idea was born.

We will create a wordpress plugin and a site that directs kit developers to fill out a form that says in 100 words why they would make interesting kits. Kit builders will be directed by the same plugin to a Woo Commerce store where they can purchase kits to make.

The parts of this project:

1. custom child theme with analytics, font and colors in the root css selector
2. 5 posts with media moved from the main page to be landing pages to help people to find kits from Google. Write the posts also with the editorial angle of how kits help deal with stress
3. 5 sample kit products with media in Woo Commerce
4. short code plugin for main page that takes a kit maker to the shopping page and kit developers to a registration page. `[split-plugin url1=https://www.google.com url2=https://github.com]`
5. a registration page with room for a 100 word summary of why the potential developer would provide good kits. When a user completes the registration page it is up to the administrator to give them permissions for the shop manager role that they can use to create kits in woocommerce.
6. as well as a starter plugin, this is a project for putting wordpress on qovery.com, a container as a service platform. Deploy this easily on qovery.com.

## Quick start

This can be used as a plugin by downloading the .zip and installing it as a plugin.

## Less quick start

To test as a complete environment on localhost you will need docker.io or docker-desktop as well as docker-compose. In the root folder run `docker-compose build && docker-compose up`. Surf to [http://localhost:8080](http://localhost:8080)

## To deploy publicly on qovery.com

1. You will need to fork this repo in to your own github.

2. Setup in a project in qovery.com

3. Clone to your local development environment

4. Edit ./qovery.yml to include:
```
application:
  ...
  publicly_accessible: true
  port: 80

  storage:
  ...

```
5. Install the qovery cli `curl -s https://get.qovery.com | sudo bash`

Then run `qovery application env list` to find your environment variables and change them in the ./wp-config.php file to match. When you do your next push it will be re-deployed on qovery.

# Embedded WordPress Docker Image

This is an image to help on plugins and themes development, but it
can be used to host a WordPress site as well. There are some tools
and nice libraries included, like *OPCache*, *X-Debug* and *WP-Cli*.

## Overriding variables not defined on wp-config.php

You can mount a folder with your configuration at `/var/www/html/wp-config.d`. All php files on that directory will be included on `wp-config.php`. You can also override variables present on default `wp-config.php`. You can also override the docker entrypoint by putting files in /docker-entrypoint-extra/ of the image. See ./root/do_chown.sh in the Dockerfile.

## Development environment

There is a WordPress installed at `/var/www/html`. So if you want
to develop a plugin, you can mount your content mapping your plugin
folder in `/var/www/html/wp-content/plugins`.
