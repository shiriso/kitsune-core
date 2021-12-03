<div style="text-align:center;">

# Kitsune

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shiriso/kitsune-core.svg)](https://packagist.org/packages/shiriso/kitsune-core)
[![Packagist Downloads](https://img.shields.io/packagist/dt/shiriso/kitsune-core.svg)](https://packagist.org/packages/shiriso/kitsune-core)
[![GitHub pull-requests](https://img.shields.io/github/issues-pr/shiriso/kitsune-core.svg)](https://github.com/shiriso/kitsune-core/pull/)
[![GitHub issues](https://img.shields.io/github/issues/shiriso/kitsune-core.svg)](https://github.com/shiriso/kitsune-core/issues/)
[![Laravel Version](https://img.shields.io/badge/Minimum_Laravel_Version-8.x-red.svg)](https://laravel.com/docs/8.x)

</div>

## About Kitsune
Kitsune is an extension package for Laravel to add the ability to override and change views on demand and add 
additional sources which can be used as well, without changing the way how you address your views in 
controllers, components, or everywhere else you might access them.

Kitsune is also highly customisable and adds the possibility for other packages to 
use its functionality to manage their views and sources, by interacting with Kitsune
during the application's boot process.

> **In short:** Kitsune is adding the possibility for easily applicable themes and external views 
> without messing with your views.
 
### How does it work?
Laravel uses a `FileViewFinder` which already implemented support for multiple paths and namespaces. 
But since it is barely documented most people don't know about these possibilities and by default 
all of your desired paths would have to be configured in your `view` config at `view.paths`.

Kitsune avoids messing around with the config files and configures the already existing `FileViewFinder` 
accordingly to your needs. Nothing changes the way you access a view or how it is rendered. 

## Installation
You can install the Kitsune Core via composer:
```
composer require shiriso/kitsune-core
```

## Usage
After the installation Kitsune is already ready to run, but nothing will change on its own. 

From here on you got various options to get started:

### Environment Configuration


### Using the Kitsune Middleware





## Package Development
Trying to develop a package which offers components to the user, but you can't say for sure where your views might
be located at?

### Why using Kitsune?
Your structure may look like this:
* Original Files: `/vendor/<provider>/<package>/resources/views`
* Published Files: `/resources/views/vendor/<package>`

If that is everything you want to support it is plenty to use [loadViewsFrom](https://laravel.com/docs/8.x/packages#views) 
which already implements the possibility to [Override Package Views](https://laravel.com/docs/8.x/packages#overriding-package-views).
Just remember to prefix your views with the given namespace, and you are ready to go.

But since you are looking at Kitsune, it is most likely that you considered supporting some kind of theming or want 
your package to support various frontend frameworks. Supporting React and Vue or Tailwind and Bootstrap,
it has never been that easy.

With Kitsune you will be able to support structures like this out of the box:
* Original Files for a given Layout: `/vendor/<provider>/<package>/resources/views/<layout>`
* Original Files: `/vendor/<provider>/<package>/resources/views`
* Published Files for a given Layout: `/resources/views/vendor/<package>/<layout>`
* Published Files: `/resources/views/vendor/<package>`

> **Special Note:**
> 
> Even if you might not support various layouts your customers are using, the most interesting part might be,
> that your customer will be able to create various layouts based on their configuration, without you or them doing
> doing anything for it. 
> 
> This will add convenience for you, as well as your users, as neither of you needs to take care of the same task
> over and over again. Why would anyone want to implement a theming support over and over again?