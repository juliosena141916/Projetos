# Site Academia Fitness

Sistema web completo para gerenciamento de academia com área do aluno e área administrativa.

## Funcionalidades Implementadas

### 🏠 Página Inicial
- **Arquivo**: `paginaInicial.html` / `paginaInicial.php`
- Interface moderna e responsiva
- Links para login de alunos e administradores
- Informações sobre a academia

### 👤 Área do Aluno

#### Cadastro de Alunos
- **Arquivo**: `paginaCadastro.html` / `paginaCadastro.php`
- Validação completa de dados
- Verificação de duplicatas (usuário, email, CPF)
- Máscaras para CPF e telefone
- Hash seguro de senhas

#### Login de Alunos
- **Arquivo**: `paginaLogin.html` / `paginaLogin.php`
- Login por usuário ou email
- Verificação de senha com hash
- Sessões seguras

#### Dashboard do Aluno
- **Arquivo**: `dashboard_aluno.php`
- Interface personalizada
- Informações da conta
- Menu de funcionalidades
- Logout seguro

### 🔐 Área Administrativa

#### Login Administrativo
- **Arquivo**: `loginAdm.html` / `loginAdm.php`
- Acesso restrito para administradores
- Verificação de credenciais

#### Dashboard Administrativo
- **Arquivo**: `dashboard_admin.php`
- Estatísticas da academia
- Menu de gerenciamento
- Controle de alunos

#### Alteração de Senha Admin
- **Arquivo**: `senhaAdm.php`
- Alteração segura de senhas
- Validação de senha atual

### 🔧 Sistema de Conexão
- **Arquivo**: `conexao.php`
- Conexão robusta com MySQL
- Tratamento de erros
- Funções auxiliares para queries
- Configuração de charset UTF-8

### 🚪 Sistema de Logout
- **Arquivos**: `logout_aluno.php` / `logoutAdm.php`
- Destruição segura de sessões
- Redirecionamento adequado

## Estrutura do Banco de Dados

### Tabela ALUNO
- `ID_ALUNO` (Primary Key)
- `USUARIO_ALUNO` (Unique)
- `EMAIL_ALUNO` (Unique)
- `CPF_ALUNO` (Unique)
- `TEL_ALUNO`
- `SENHA_HASH`

### Tabela ADMIN
- `ID_ADM` (Primary Key)
- `USUARIO_ADM` (Unique)
- `SENHA_ADM` (Hash)

## Configuração

1. **Banco de Dados**: Configure as credenciais em `conexao.php`
2. **Servidor Web**: Apache/Nginx com PHP 7.4+
3. **MySQL**: Versão 5.7+ ou MariaDB 10.3+

## Segurança Implementada

- ✅ Hash de senhas com `password_hash()`
- ✅ Prepared statements para prevenir SQL injection
- ✅ Validação de entrada de dados
- ✅ Sessões seguras
- ✅ Sanitização de saída com `htmlspecialchars()`
- ✅ Tratamento de erros

## Navegação do Site

```
paginaInicial.html
├── paginaLogin.html (Alunos)
│   └── dashboard_aluno.php
├── paginaCadastro.html (Novos alunos)
├── loginAdm.html (Administradores)
│   └── dashboard_admin.php
│       └── senhaAdm.php
└── logout_aluno.php / logoutAdm.php
```

## Status das Funcionalidades

- ✅ Cadastro de alunos
- ✅ Login de alunos
- ✅ Dashboard do aluno
- ✅ Login administrativo
- ✅ Dashboard administrativo
- ✅ Alteração de senha admin
- ✅ Sistema de logout
- ✅ Conexão com banco de dados
- ✅ Validações e segurança

## Próximas Funcionalidades Sugeridas

- 📊 Relatórios detalhados
- 💳 Sistema de pagamentos
- 🏋️ Gerenciamento de treinos
- 📅 Sistema de agendamentos
- 📱 API para aplicativo móvel
- 📧 Sistema de notificações por email
