# Integrações disponíveis

Os dados de acesso de cada integração encontra-se aqui:
https://projetos.codificar.com.br/projects/desenvolvimento/wiki/Acessos_aos_Marketplaces

## iFood

Esse módulo atualmente somente está compatível com o iFood, pois é o principal marketplace do Brasi.

A documentação do iFood pode ser acessada pela URL:

https://developer.ifood.com.br/pt-BR/docs/guides

### Gerar um pedido de Testes


Leia atentamente a documentação: https://developer.ifood.com.br/pt-BR/docs/guides/order/workflow/#testes

E solicite ao gerente o usuário de testes da Codificar, para iniciar o procedimento.

Mas em resumo os procedimentos são:

1. Faça login no iFood.com.br com os dados de desenvolvedor da Codificar
2. Insira o endereço "Ramal Bujari, 100" e depois confirme a localização. No campo bairro informe "Bujari" e clique em "Salvar Endereço"
3. Rode o comando de polling para abrir a loja: `php artisan marketplace:polling`
4. Vá na loja Teste da Codificar e faça um pedido de compra: https://www.ifood.com.br/delivery/bujari-ac/teste---codificar-sistemas-3282-bujari/9fbd626b-723b-4bb0-86d8-0a75b23285b6
5. Caso o supervisor não esteja habilitado, rode o comando `php artisan marketplace:polling` sempre que desejar atualizar o status do pedido
6. Para aceitar o pedido no windows baixe o gestor de pedidos do Ifood: https://gestordepedidos.ifood.com.br/#/download
7. No Linux, abra o arquivo insomnia.json na pasta root deste projeto e execute a confirmação do pedido


## Hubster

A API do Hubster tem dois tipos de integração, nessa versão atual do código temos apenas integração com o POS e não com a parte de delivery.

Mas o que importa para nós é exatamente o POS, que recebe o pedido criado, independente da plataforma.

### Gerar um pedido de Testes

Segue passos para testar:

1. Faça o login em: https://app-staging.tryhubster.com/orders. Solicite os dados de login para o cliente que deseja homologar, ou acesse a WIKI da Codificar para ter acesso aos dados. https://projetos.codificar.com.br/projects/desenvolvimento/wiki/Acessos_aos_Marketplaces?parent=Wiki
2. Vá na diagonal direita superior e clique em adicionar um novo pedido. 
3. Escolha a opção de delivery, preencha os dados de endereço , selecione os itens do pedido e faça o pedido.
4. Nesse momento deverá chegar um webhook de novo pedido no controller: MarketplaceController::webhook

Para visualizar se o webhook e o APP está configurado, faça os seguintes passos:

1. Acesse e faça o login https://developer-staging.tryhubster.com/applications/c8f9a164-ac52-486f-bb85-74c3c7cc0518/event-logs
2. Confira se os logs estão corretos para o APP.

# Ambiente de Dev

Abra uma nova janela do Visual Studio e selecione a pasta do vendor/codificar/marketplace-integration

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
