# Ambiente de Dev

Abra uma nova janela do Visual Studio e selecione a pasta do vendor/codificar/marketplace-integration

## Instale as libs

Instale as libs para desenvolvimento com o comando `yarn install`

## Escute as modificações 

Para atualizar o projeto em dev em tempo real, use o comando `yarn watch` que irá salvar as atualizações feitas e publicará na pasta do projeto que o compete

# Ambiente de Publicação

Altere o composer.json do projeto

## Require

```
require:{
        "codificar/marketplace-integration":"dev-master",
}
```
## Repositories

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
```

## PSR-4

```
psr-4:{
    "Codificar\\MarketplaceIntegration\\": "vendor/codificar/marketplace-integration/src",

}
```

## Configuração da Lib como Provider do Laravel 

Abra o arquivo `config/app.php`

Adicione mais um provider

'providers' => [
        Codificar\MarketplaceIntegration\MarketplaceServiceProvider::class,
],
