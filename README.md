### Author: José Roberto
#### Email: tekinforroberto@gmail.com

<br />

### About

`apidevbarber` é uma API para aplicativo mobile de agendamento de serviços de barbeiro, desenvolvida na linguagem PHP e no Laravel Framework.

<br />

### Tecnologia utilizadas

- PHP
- Laravel
- MySql

#### Getting started

```bash
$ git clone https://github.com/roberto-reis/apidevbarber.git
```

```bash
$ cd apidevbarber
```

```bash
$ composer install
```

Copiar o env.example e renomear para .env e set as configurações do banco de dados

Executar as mingrations
```bash
$ php artisan migrate
```

Gerar a key
```bash
$ php artisan key:generate
```

Gerar a secret key JWT
```bash
$ php artisan jwt:secret
```

Up projeto
```bash
$ php artisan serve
```

<br />

### About
`apidevbarber` é um software de código aberto licenciado sob a [MIT license](https://opensource.org/licenses/MIT).