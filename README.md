# Qual é a música? - Implementação Completa

**Versão:** 1.0  
**Status:** Pronto para Uso  
**Última Atualização:** 16 de abril de 2026

---

## 📋 Estrutura do Projeto

```
qualamusica/
├── index.php                    # Front controller principal
├── .env                         # Variáveis de ambiente (não versionável)
├── .htaccess                    # Reescrita de URLs
│
├── api/                         # Backend - Lógica de Negócio
│   ├── Core/
│   │   ├── Config.php          # Carregador de .env
│   │   └── Database.php        # Conexão PDO e queries
│   ├── Controllers/
│   │   └── MusicController.php # Endpoints AJAX
│   ├── Models/
│   │   └── Music.php           # Entidade Música
│   ├── Repositories/
│   │   └── MusicRepository.php # Acesso a dados
│   └── Services/
│       └── MusicService.php    # Lógica de negócio
│
├── app/                         # Frontend - Interface
│   ├── views/
│   │   ├── home.php            # Seleção de músicas
│   │   ├── admin.php           # Cadastro/edição de músicas
│   │   ├── tocando.php         # Reprodução karaokê
│   │   └── revelar.php         # Exibição de clipe + dados
│   └── assets/
│       ├── js/                 # Scripts customizados
│       ├── css/                # Estilos customizados
│       └── img/                # Imagens
│
└── zz_Temp/                     # Arquivos auxiliares
    ├── Layout/                  # Layouts base de referência
    ├── MDs/                     # Documentação (PRD, rules)
    └── migration_*.php          # Migrations do banco
```

---

## 🚀 Guia de Instalação

### 1. Pré-requisitos

- **PHP 7.4+** (com suporte a PDO)
- **MySQL 5.7+** (ou MariaDB)
- **Servidor Web** (Apache, Nginx, etc com suporte a `.htaccess` ou reescrita de URLs)
- **Navegador moderno** (Chrome, Firefox, Safari, Edge)

### 2. Configuração do Banco de Dados

#### Via MySQL CLI:

```bash
mysql -u root -p qualamusica < zz_Temp/migration_create_musicas_table.php
```

#### Via phpMyAdmin ou similar:

1. Crie um novo banco de dados chamado `qualamusica`
2. Execute o SQL contido em `zz_Temp/migration_create_musicas_table.php`

#### Ou manualmente no MySQL:

```sql
CREATE DATABASE IF NOT EXISTS qualamusica 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE qualamusica;

CREATE TABLE IF NOT EXISTS musicas (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    cantor VARCHAR(255) NOT NULL,
    autor VARCHAR(255) NOT NULL,
    link_karaoke VARCHAR(500) NOT NULL,
    link_clipe VARCHAR(500) NOT NULL,
    ano YEAR NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Configuração do `.env`

Edite o arquivo `.env` na raiz com suas credenciais:

```env
# Banco de Dados
DB_HOST=localhost
DB_PORT=3306
DB_NAME=qualamusica
DB_USER=root
DB_PASS=sua_senha_aqui

# Aplicação
APP_NAME="Qual é a música?"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080/qualamusica

# Segurança
ADMIN_SECRET_KEY=change_this_secret_key_in_production
```

### 4. Deploy em Servidor Local

#### Com PHP Built-in Server:

```bash
cd /path/to/qualamusica
php -S localhost:8080
# Acesse: http://localhost:8080/qualamusica
```

#### Com Apache XAMPP:

1. Coloque a pasta em `xampp/htdocs/qualamusica`
2. Acesse: `http://localhost/qualamusica/`

#### Com Nginx:

Configure um bloco `server` apontando para o diretório raiz do projeto.

---

## 🎮 Guia de Uso

### Para Jogadores

1. **Acesse a Home** → `http://localhost:8080/qualamusica/`
2. **Escolha uma música** → Clique em qualquer card
3. **Escute o karaokê** → Tela "Tocando" (sem revelar resposta)
4. **Clique em "Revelar"** → Vá para "Revelar"
5. **Veja o clipe + dados** → Nome, cantor, autor e ano

### Para Administradores

1. **Acesse a Home** → `http://localhost:8080/qualamusica/`
2. **Clique no botão 🔒 (cadeado)** → Painel Admin
3. **Preencha o formulário**
   - Nome da Música
   - Cantor/Artista
   - Autor/Compositor
   - Link Karaokê (URL do YouTube)
   - Link Clipe (URL do YouTube)
   - Ano de Lançamento

4. **Clique em "Salvar Todos"** → Música cadastrada via AJAX
5. **Veja feedback Toastr.js** → Sucesso ou erro automático

#### Validações Implementadas

- ✅ Campos obrigatórios
- ✅ Validação de URLs do YouTube
- ✅ Suporte a múltiplos formatos de YouTube
- ✅ Feedback visual em tempo real (Toastr)
- ✅ Validação de acentuação (UTF-8/pt-BR)

---

## 🔑 Endpoints da API

### GET /api/musicas
**Retorna:** Lista de todas as músicas em JSON

```json
{
  "success": true,
  "message": "Músicas listadas com sucesso",
  "data": [...],
  "total": 5
}
```

### POST /api/musicas
**Cria:** Uma nova música

**Body (JSON):**
```json
{
  "nome": "Nome da Música",
  "cantor": "Cantor",
  "autor": "Autor",
  "link_karaoke": "https://www.youtube.com/watch?v=ID",
  "link_clipe": "https://www.youtube.com/watch?v=ID",
  "ano": 2020
}
```

### PUT /api/musicas/{id}
**Atualiza:** Uma música existente

### DELETE /api/musicas/{id}
**Deleta:** Uma música existente

---

## 🛠️ Tecnologias Utilizadas

| Tecnologia | Propósito |
|------------|-----------|
| **PHP 7.4+** | Backend MVC |
| **PDO** | Acesso seguro ao banco |
| **MySQL/MariaDB** | Persistência de dados |
| **HTML5** | Estrutura semântica |
| **Tailwind CSS** | Estilos responsivos |
| **Bootstrap** | Componentes UI |
| **JavaScript (Vanilla)** | Interatividade frontend |
| **AJAX/Fetch API** | Requisições assíncronas |
| **Toastr.js** | Notificações toast |
| **YouTube Embed API** | Reprodução de vídeos |
| **Apache .htaccess** | Reescrita de URLs |

---

## 📐 Padrões de Arquitetura

### MVC (Model-View-Controller)

```
Request → index.php (Front Controller)
              ↓
         Roteador
              ↓
    Controller → Service → Repository → Database
              ↓
         View (renderiza PHP)
              ↓
         Response (HTML/JSON)
```

### Orientação a Objeto (OO)

- **Classes bem definidas** com responsabilidade única
- **Encapsulamento** de dados sensíveis
- **Herança e composição** quando apropriado
- **Type hints** para maior segurança de tipos

### Segurança

- ✅ **PDO com Prepared Statements** - Previne SQL injection
- ✅ **Validação de entrada** - Sanitização de dados
- ✅ **Dados sensíveis em .env** - Nunca hardcoded
- ✅ **Charset UTF-8mb4** - Suporte a acentuação
- ✅ **HTTPS-ready** - Pronto para produção
- ✅ **Headers de segurança** - X-Frame-Options, X-Content-Type-Options, etc

---

## 🎨 Identidade Visual

- **Cores:** Paleta Material Design 3
- **Font:** Lexend (Google Fonts)
- **Icons:** Material Symbols Outlined
- **Responsive:** Mobile First (adapta para tablet e desktop)
- **Animações:** Transições suaves e efeitos lúdicos
- **Tema:** Jogo interativo e divertido

---

## 🧪 Testes Manuais

### Fluxo 1: Jogar

- [ ] Home carrega corretamente
- [ ] Todas as músicas aparecem como cards
- [ ] Clique em música vai para "Tocando"
- [ ] Vídeo karaokê reproduz
- [ ] Nenhum dado da música aparece em "Tocando"
- [ ] Botão "Revelar" funciona
- [ ] "Revelar" mostra todos os dados + clipe
- [ ] Botões "Voltar" e "Próxima" funcionam

### Fluxo 2: Admin

- [ ] Botão 🔒 na home leva ao admin
- [ ] Formulário tem 6 campos corretos
- [ ] Botão "+ Adicionar Linha" funciona
- [ ] Preenchimento de linha múltipla funciona
- [ ] Validação de YouTube (rejeita URLs inválidas)
- [ ] Botão "Salvar Todos" faz AJAX
- [ ] Toastr mostra sucesso/erro
- [ ] Página NÃO recarrega após salvar
- [ ] Nova música aparece na lista

### Fluxo 3: Banco de Dados

- [ ] Conexão ao MySQL funciona
- [ ] Dados salvos com charset UTF-8mb4
- [ ] Acentos salvos e recuperados corretamente
- [ ] Timestamps (criado_em, atualizado_em) funcionam

### Fluxo 4: Experiência

- [ ] Home é intuitiva e chamativa
- [ ] Transições suaves em todas as páginas
- [ ] Icons carregam corretamente
- [ ] Font Lexend renderiza bem
- [ ] Responsive em mobile/tablet/desktop
- [ ] SEM erros no console JavaScript

---

## 📱 Responsividade

### Mobile (< 640px)
- Cards em coluna única
- Botões ocupam largura total
- Nav horizontal otimizada
- Texto ajustado para leitura

### Tablet (640px - 1024px)
- Grid 2-3 colunas
- Sidebar nav aparece
- Elementos espaçados

### Desktop (> 1024px)
- Grid completo assimétrico (Bento)
- Sidebar fixo
- Elementos otimizados

---

## 🔐 Variáveis de Ambiente Obrigatórias

```env
DB_HOST=localhost            # Host do MySQL
DB_PORT=3306                 # Porta (padrão: 3306)
DB_NAME=qualamusica          # Nome do banco
DB_USER=root                 # Usuário MySQL
DB_PASS=                     # Senha MySQL

APP_NAME=Qual é a música?    # Nome da aplicação
APP_ENV=development          # development|production
APP_DEBUG=true               # true|false (sempre false em produção!)
APP_URL=http://localhost:8080/qualamusica  # URL base

ADMIN_SECRET_KEY=change_me   # Chave para autenticação futura
```

---

## 🐛 Debug & Troubleshooting

### Erro: "Arquivo .env não encontrado"
**Solução:** Certifique-se que `.env` está na raiz do projeto

### Erro: "Erro ao conectar no banco"
**Solução:** Verifique credenciais em `.env` e se o MySQL está rodando

### Vídeo do YouTube não carrega
**Solução:** Verifique se a URL é válida e se o vídeo é público

### Acentos aparecem quebrados
**Solução:** Verifique se o charset é `utf8mb4` no MySQL e no `.env`

### AJAX não funciona
**Solução:** Verifique se o URL em `fetch()` está correto (relativo ao base path)

---

## 📝 Checklist de Implantação

- [ ] `.env` configurado com credenciais reais
- [ ] Banco de dados criado
- [ ] Migration executada
- [ ] Folder permissions corretos (755 para pastas, 644 para arquivos)
- [ ] `.htaccess` habilitado (AllowOverride All)
- [ ] URL base correta em `.env` e `index.php`
- [ ] SSL/HTTPS configurado (para produção)
- [ ] Backup do banco de dados
- [ ] Testes de funcionalidade completos
- [ ] Monitoramento de erros ativo

---

## 📞 Suporte & Contribuições

Este projeto foi desenvolvido seguindo rigorosamente:
- **PRD.md** - Visão, requisitos, fluxos
- **project_rules.md** - Regras obrigatórias, arquitetura, padrões

Para alterações futuras, revise sempre esses documentos.

---

## 📄 Licença

Projeto desenvolvido para fins educacionais e institucionais.

**Desenvolvido com ❤️ - Qual é a música? V1.0**
#   q u a l a m u s i c a  
 