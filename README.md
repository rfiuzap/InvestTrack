# InvestTrack Finance

O InvestTrack Finance é uma plataforma para controle e acompanhamento de investimentos em ações e outros ativos, com gestão de carteira, operações, proventos e relatórios analíticos.

## Objetivo do projeto

Este projeto foi pensado para ser executado **localmente na máquina do usuário**. O objetivo não é manter uma plataforma online nem capturar, armazenar ou centralizar informações financeiras pessoais na internet.

Os dados registrados pelo usuário ficam no banco de dados configurado na própria máquina. Por isso, a responsabilidade por manter o computador protegido e realizar cópias de segurança dos dados é do usuário.

## Requisitos

- Windows;
- [XAMPP](https://www.apachefriends.org/pt_br/index.html), com Apache, PHP e MySQL/MariaDB;
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
4. Inicie os módulos **Apache** e **MySQL**.

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

## Configuração do banco de dados

1. Com o MySQL iniciado no XAMPP, abra no navegador:

   [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

2. No phpMyAdmin, abra a aba **Importar**.
3. Selecione o arquivo:

   ```text
   o script SQL de estrutura do banco local
   ```

4. Execute a importação para criar as tabelas do banco local.
5. Para uma instalação local, configure `config/database.php` com os dados do seu banco privado:

   - servidor: `localhost`;
   - usuário: `root`;
   - senha: vazia, como é comum na instalação padrão do XAMPP.

   A versão publicada no GitHub vem apontada para `banco_producao`. O banco local deve permanecer fora do repositório e nunca ser enviado ao GitHub. Caso a instalação do MySQL use outra senha ou porta, ajuste esse arquivo antes de acessar a aplicação.

### Banco limpo para o GitHub

Para publicar ou demonstrar o projeto sem levar os registros do ambiente local, use o arquivo `database/banco_producao.sql`. Ele cria um banco separado chamado `banco_producao`, com:

- as tabelas necessárias para a aplicação;
- duas carteiras de demonstração: João e Maria;
- dez ações de teste;
- dez operações de compra para cada carteira, totalizando vinte operações.

Esse arquivo não apaga nem modifica os dados do ambiente local. Depois de importá-lo, configure `config/database.php` para usar:

```php
$dbname = 'banco_producao';
```

O arquivo `database/banco_producao.sql` contém apenas dados fictícios para demonstração e testes. Os scripts e dados do ambiente local não fazem parte da publicação.

## Acessando a aplicação

Com Apache e MySQL em execução, abra no navegador:

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

Como os dados ficam na instalação local, faça cópias de segurança periódicas pelo phpMyAdmin, especialmente antes de reinstalar o XAMPP ou mover o projeto para outro computador.

## Versão do projeto

A versão atual fica no arquivo `VERSION` e aparece automaticamente abaixo de **Finance Pro** no menu lateral. Para atualizar a versão sem editar o layout, execute na raiz do projeto:

```text
php scripts/update-version.php patch
```

Use `patch` para correções, `minor` para novas funcionalidades compatíveis ou `major` para mudanças incompatíveis. Por exemplo:

```text
php scripts/update-version.php minor
```
