```
repositories:{
    {
      "type":"package",
      "package": {
          "name": "codificar/marketplace-integration",
          "version":"master",
          "source": {
              "url": "https://libs:ofImhksJ@git.codificar.com.br/laravel-libs//marketplace-integration.git",
              "type": "git",
              "reference":"master"
          }
        }
    },
}

require:{
        "codificar/marketplace-integration":"dev-master",
}


psr-4:{
    "Codificar\\MarketPlaceIntegration\\": "vendor/codificar/marketplace-integrations/src",

}



/config/app.php:[
        Codificar\MarketPlaceIntegration\MarketplaceServiceProvider::class,
],
```