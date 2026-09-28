# InvestTrack Finance

O InvestTrack Finance é uma plataforma para controle e acompanhamento de investimentos em ações e outros ativos, com gestão de carteira, operações, proventos e relatórios analíticos.

## Objetivo do projeto

Este projeto foi pensado para ser executado **localmente na máquina do usuário**. O objetivo não é manter uma plataforma online nem capturar, armazenar ou centralizar informações financeiras pessoais na internet.

Os dados registrados pelo usuário ficam no banco de dados configurado na própria máquina. Por isso, a responsabilidade por manter o computador protegido e realizar cópias de segurança dos dados é do usuário.

## Requisitos

- Windows;
- [XAMPP](https://www.apachefriends.org/pt_br/index.html), com Apache e PHP habilitado para SQLite (`pdo_sqlite`);
- Um navegador moderno;
- Os arquivos deste projeto baixados do GitHub.

## Instalação do XAMPP

1. Baixe e instale o XAMPP pelo site oficial.
2. O diretório padrão de instalação costuma ser:

   ```text
   C:\xampp
   ```

   Também é possível instalar em outro diretório, como `D:\xampp`. Neste caso, use a pasta `htdocs` dentro do diretório escolhido.
3. Abra o **XAMPP Control Panel**.
4. Inicie o módulo **Apache**. O projeto não precisa de servidor MySQL.

## Onde colocar os arquivos do GitHub

Os arquivos de aplicações PHP precisam ficar dentro da pasta `htdocs` do XAMPP. Uma organização recomendada é:

```text
C:\xampp\htdocs\Projetos\InvestTrack Finance
```

Depois de baixar ou clonar este repositório, confirme que arquivos como `index.php`, `bootstrap.php`, `dashboard.php` e as pastas `api`, `config`, `controllers`, `database`, `models`, `services` e `views` estão diretamente dentro da pasta do projeto.

Por exemplo, o arquivo principal deve estar em:

```text
C:\xampp\htdocs\Projetos\InvestTrack Finance\index.php
```

Se o XAMPP estiver instalado em outro local, substitua `C:\xampp` pelo diretório escolhido. O projeto pode ficar diretamente em `htdocs` ou em uma subpasta, por exemplo:

```text
D:\xampp\htdocs\InvestTrack Finance
```

## Banco de dados

O projeto usa SQLite e cria as tabelas automaticamente na primeira execução, a partir de `database/schema.sqlite.sql`. Os modos selecionados por `index.php` e `local.php` usam arquivos separados: `banco_producao.sqlite` e `banco_local.sqlite`. O modo local cria as carteiras vazias Renato e Vicente. Já o `index.php` carrega automaticamente o conjunto demonstrativo no banco de produção quando ele ainda não tem ativos: João e Maria, dez ativos e vinte operações.

Na demonstração, é permitido criar e inativar carteiras para testar o sistema. Essas alterações são temporárias: após duas horas da última reinicialização, o próximo acesso ao banco restaura automaticamente o conjunto original de demonstração, com João e Maria ativos e os dados temporários removidos.

Na publicação em hospedagem, envie também o arquivo `database/banco_producao.sql`. Ele é o seed obrigatório usado para criar e restaurar o banco de demonstração; sem esse arquivo, a aplicação não consegue inicializar o ambiente demonstrativo.

Por padrão, os arquivos ficam fora da pasta pública do site: em uma hospedagem com `public_html`, por exemplo, ficam em `../investtrack-data/`. No XAMPP, ficam em `C:\xampp\investtrack-data\`. O usuário do PHP precisa ter permissão de escrita nesse diretório.

Se precisar escolher outro diretório gravável, configure `INVESTTRACK_SQLITE_DIR` no ambiente do PHP com o caminho absoluto. Em hospedagem, mantenha os arquivos fora do document root e faça backup periódico.

Os bancos MySQL existentes não são apagados nem importados automaticamente. Para importar o banco local existente, faça primeiro um backup do SQLite e execute `php scripts/migrate_mysql_local_to_sqlite.php` no terminal, definindo `INVESTTRACK_SQLITE_DIR` se o diretório de dados não for o padrão. O utilitário importa somente `banco_local`, preserva os IDs e aborta se as tabelas de destino já tiverem registros ou se as carteiras forem diferentes. As credenciais de origem podem ser definidas por `INVESTTRACK_MYSQL_HOST`, `INVESTTRACK_MYSQL_DATABASE`, `INVESTTRACK_MYSQL_USER`, `INVESTTRACK_MYSQL_PASSWORD` e `INVESTTRACK_MYSQL_PORT`.

## Acessando a aplicação

Com o Apache em execução, abra no navegador:

```text
http://localhost/Projetos/InvestTrack%20Finance/
```

O espaço no nome da pasta aparece como `%20` no endereço. Também é possível acessar as páginas diretamente, como:

```text
http://localhost/Projetos/InvestTrack%20Finance/dashboard.php
```

Se você colocar o projeto em outra pasta, o endereço deve refletir esse caminho. Além disso, confira o valor de `BASE_URL` em `config/app.php` para que os links e redirecionamentos funcionem corretamente.

## Uso e responsabilidade dos dados

O InvestTrack Finance é uma ferramenta local de organização e acompanhamento. Ele não substitui orientação profissional de investimentos, não executa ordens em corretoras e não deve ser tratado como fonte única para decisões financeiras.

Como os dados ficam em um arquivo SQLite privado, faça cópias de segurança periódicas desse arquivo, especialmente antes de reinstalar o XAMPP ou mover o projeto para outro computador.

## Versão do projeto

A versão atual fica no arquivo `VERSION` e aparece automaticamente abaixo de **Finance Pro** no menu lateral. Para atualizar a versão sem editar o layout, execute na raiz do projeto:

```text
php scripts/update-version.php patch
```

Use `patch` para correções, `minor` para novas funcionalidades compatíveis ou `major` para mudanças incompatíveis. Por exemplo:

```text
php scripts/update-version.php minor
```
