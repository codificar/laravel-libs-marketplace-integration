# Integrações disponíveis

Os dados de acesso de cada integração encontra-se aqui:
https://projetos.codificar.com.br/projects/desenvolvimento/wiki/Acessos_aos_Marketplaces

Na URL acima tem todos os dados de acessos e também como fazer os pedidos de testes para simulação.
# Testes unitários

Antes execute o `composer install`

```
    
```

## Yarn

### Versão do node

Aplique a versão 12 do node com o comando `nvm use 12` ou `nvm install 12`.

Caso não tenha instalado o NVM instale de acordo com seu sistema operacional.
### Instale as libs do node_modules

Instale as libs para desenvolvimento com o comando `yarn install`

# Ambiente de Publicação

Altere o composer.json do projetos
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
