# VIES-WEB

Quick and easy interface to validate VAT Information Exchange System (VIES) of the European Commission (EC). You can access it at [vies-web.azurewebsites.net](http://vies-web.azurewebsites.net) to try it out.

No guarantees provided.

## Run it locally with Docker

You can now run this yourself in an isolated container using [Docker](https://www.docker.com).

```shell
docker run --name vies_webapp --rm -d -p 8000:18080 dragonbe/vies-web 
```

## Want to use it yourself?

This VIES client is now available for usage in your own applications. You can directly fork it from [GitHub](https://github.com/dragonbe/vies-web) or use the package from [Packagist](https://packagist.org/dragonbe/vies).

# LICENCE

This software is provided as-is under [MIT licence](LICENCE).