<?php
/**
 * Front Controller Principal - Qual é a música?
 * 
 * Responsável por:
 * - Bootstrap da aplicação
 * - Carregamento de dependências
 * - Despacho de rotas
 * - Tratamento de erros global
 */

// Define raiz do projeto
define('ROOT_PATH', __DIR__);

// Sessão para autenticação do admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload simples para classes PHP
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/api/Core/',
        ROOT_PATH . '/api/Controllers/',
        ROOT_PATH . '/api/Models/',
        ROOT_PATH . '/api/Repositories/',
        ROOT_PATH . '/api/Services/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});

// Headers padrão
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Carrega configurações
try {
    Config::load();
} catch (Exception $e) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'message' => 'Erro ao carregar configurações',
        'errors' => Config::getBool('APP_DEBUG', false) ? [$e->getMessage()] : []
    ]));
}

// Obtém a URI requisição
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = app_normalize_route((string) $requestUri);

// Roteamento
try {
    // Se for requisição de API
    if (strpos($route, '/api/') === 0) {
        $apiRoute = substr($route, 5); // Remove "/api/"
        handleApiRequest($apiRoute);
    }
    // Se for requisição de página
    else {
        handlePageRequest($route);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar requisição',
        'errors' => Config::getBool('APP_DEBUG', false) ? [$e->getMessage()] : []
    ]);
}

/**
 * Verifica se a requisição atual veio de um host local.
 */
function app_is_local_request(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    if ($host === '') {
        return false;
    }

    $host = explode(':', $host)[0];
    return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
}

/**
 * Normaliza base path em formato seguro para roteamento.
 */
function app_normalize_base_path(string $path): string
{
    $trimmed = trim($path);
    if ($trimmed === '' || $trimmed === '/') {
        return '';
    }

    return '/' . trim($trimmed, '/');
}

/**
 * Retorna base path atual considerando localhost e APP_ENV.
 */
function app_base_path(): string
{
    static $resolved = null;
    if ($resolved !== null) {
        return $resolved;
    }

    $override = trim((string) Config::get('APP_BASE_PATH', ''));
    if ($override !== '') {
        $resolved = app_normalize_base_path($override);
        return $resolved;
    }

    $localBasePath = app_normalize_base_path((string) Config::get('APP_LOCAL_BASE_PATH', '/qualamusica'));
    $prodBasePath = app_normalize_base_path((string) Config::get('APP_PROD_BASE_PATH', '/'));
    $appEnv = strtolower((string) Config::get('APP_ENV', 'development'));

    if (app_is_local_request()) {
        $resolved = $localBasePath;
        return $resolved;
    }

    $resolved = $appEnv === 'production' ? $prodBasePath : $localBasePath;
    return $resolved;
}

/**
 * Retorna URL base do ambiente atual.
 */
function app_base_url(): string
{
    static $resolved = null;
    if ($resolved !== null) {
        return $resolved;
    }

    $legacyUrl = trim((string) Config::get('APP_URL', ''));
    $localUrl = trim((string) Config::get('APP_LOCAL_URL', $legacyUrl !== '' ? $legacyUrl : 'http://localhost/qualamusica'));
    $prodUrl = trim((string) Config::get('APP_PROD_URL', 'https://qualamusica.iteva.com.br'));
    $appEnv = strtolower((string) Config::get('APP_ENV', 'development'));

    if (app_is_local_request()) {
        $resolved = rtrim($localUrl, '/');
        return $resolved;
    }

    $resolved = rtrim($appEnv === 'production' ? $prodUrl : $localUrl, '/');
    return $resolved;
}

/**
 * Monta URL relativa para rotas internas.
 */
function app_url(string $path = '/'): string
{
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return app_base_path() . '/';
    }

    if ($path[0] !== '/') {
        $path = '/' . $path;
    }

    return app_base_path() . $path;
}

/**
 * Normaliza a rota recebida com base no base path ativo.
 */
function app_normalize_route(string $requestUri): string
{
    $route = $requestUri;
    $basePath = app_base_path();
    $localBasePath = app_normalize_base_path((string) Config::get('APP_LOCAL_BASE_PATH', '/qualamusica'));

    if ($basePath !== '' && str_starts_with($route, $basePath)) {
        $route = substr($route, strlen($basePath));
    }
    elseif ($localBasePath !== '' && str_starts_with($route, $localBasePath)) {
        // Compatibilidade para links antigos com /qualamusica em produção.
        $route = substr($route, strlen($localBasePath));
    }

    $route = '/' . trim($route, '/');
    return $route === '//' ? '/' : $route;
}

/**
 * Manipula requisições de API (AJAX)
 */
function handleApiRequest(string $route): void
{
    $parts = array_filter(explode('/', $route));
    $parts = array_values($parts); // Re-indexa array

    if (empty($parts)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API endpoint não encontrado']);
        return;
    }

    $resource = $parts[0] ?? null;
    $method = $_SERVER['REQUEST_METHOD'];

    // Roteamento de API
    match ($resource) {
        'musicas' => handleMusicController($method, $parts),
        default => handleNotFound()
    };
}

/**
 * Manipula requisições de página
 */
function handlePageRequest(string $route): void
{
    // Remove header de JSON para páginas
    header('Content-Type: text/html; charset=utf-8');

    $route = rtrim($route, '/') ?: '/';

    // Roteamento de páginas
    match ($route) {
        '/' => renderPage('home'),
        '/tocando' => renderPage('tocando'),
        '/revelar' => renderPage('revelar'),
        '/email' => handleEmailPage(),
        '/sorteio' => handleSorteioPage(),
        '/sorteio/pdf' => handleSorteioPdf(),
        '/admin' => handleAdminPage(),
        '/admin/logout' => handleAdminLogout(),
        default => handleNotFound()
    };
}

/**
 * Página de envio de convite por e-mail.
 */
function handleEmailPage(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'POST') {
        $recipient = trim((string) ($_POST['recipient_email'] ?? ''));

        if ($recipient === '') {
            $_SESSION['email_flash_error'] = 'Informe um e-mail de destino.';
            header('Location: ' . app_url('/email'));
            exit;
        }

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['email_flash_error'] = 'Digite um e-mail válido.';
            $_SESSION['email_flash_input'] = $recipient;
            header('Location: ' . app_url('/email'));
            exit;
        }

        require_once ROOT_PATH . '/app/services/InvitationEmailService.php';

        try {
            $service = new InvitationEmailService();
            $service->sendInvitation($recipient);
            $_SESSION['email_flash_success'] = 'Convite enviado com sucesso para ' . $recipient . '.';
            unset($_SESSION['email_flash_input']);
        } catch (Exception $e) {
            $_SESSION['email_flash_error'] = $e->getMessage();
            $_SESSION['email_flash_input'] = $recipient;
        }

        header('Location: ' . app_url('/email'));
        exit;
    }

    renderPage('email');
}

/**
 * Página de sorteio para geração do PDF.
 */
function handleSorteioPage(): void
{
    renderPage('sorteio');
}

/**
 * Gera PDF com grade de números para sorteio.
 */
function handleSorteioPdf(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['GET', 'POST'], true)) {
        http_response_code(405);
        echo 'Método não permitido';
        return;
    }

    $rawQuantity = $method === 'POST'
        ? ($_POST['quantidade'] ?? null)
        : ($_GET['quantidade'] ?? null);

    $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1,
            'max_range' => 100,
        ]
    ]);

    if ($quantity === false) {
        header('Location: ' . app_url('/sorteio?erro=Informe+um+numero+inteiro+de+1+a+100'));
        exit;
    }

    require_once ROOT_PATH . '/app/services/SorteioPdfGenerator.php';
    $generator = new SorteioPdfGenerator();
    $pdfContent = $generator->generate((int) $quantity);

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="sorteio-1-a-' . $quantity . '.pdf"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo $pdfContent;
    exit;
}

/**
 * Fluxo da página admin com autenticação por senha fixa no .env
 */
function handleAdminPage(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'POST') {
        $password = trim($_POST['admin_password'] ?? '');
        $configuredPassword = (string) Config::get('ADMIN_PANEL_PASSWORD', '');

        if ($configuredPassword === '') {
            http_response_code(500);
            renderPage('admin_login');
            return;
        }

        if (hash_equals($configuredPassword, $password)) {
            $_SESSION['admin_authenticated'] = true;
            header('Location: ' . app_url('/admin'));
            exit;
        }

        $_SESSION['admin_login_error'] = 'Senha inválida.';
        header('Location: ' . app_url('/admin'));
        exit;
    }

    if (!isAdminAuthenticated()) {
        renderPage('admin_login');
        return;
    }

    renderPage('admin');
}

/**
 * Logout do admin
 */
function handleAdminLogout(): void
{
    unset($_SESSION['admin_authenticated']);
    $_SESSION['admin_login_success'] = 'Sessão administrativa encerrada.';
    header('Location: ' . app_url('/admin'));
    exit;
}

/**
 * Renderiza uma página view
 */
function renderPage(string $page): void
{
    $viewFile = ROOT_PATH . '/app/views/' . $page . '.php';

    if (!file_exists($viewFile)) {
        http_response_code(404);
        echo '<h1>Página não encontrada</h1>';
        return;
    }

    include $viewFile;
}

/**
 * Manipula requisições para controller de Músicas
 */
function handleMusicController(string $method, array $parts): void
{
    header('Content-Type: application/json; charset=utf-8');

    $controller = new MusicController();
    $subResource = $parts[1] ?? null;
    $subAction = $parts[2] ?? null;

    if ($method === 'POST' && $subResource === 'import-csv' && $subAction === 'validate') {
        requireAdminApiAuth(fn() => $controller->validateCsv());
        return;
    }

    if ($method === 'POST' && $subResource === 'import-csv') {
        requireAdminApiAuth(fn() => $controller->importCsv());
        return;
    }

    match ($method) {
        'GET' => $controller->list(),
        'POST' => requireAdminApiAuth(fn() => $controller->store()),
        'PUT' => requireAdminApiAuth(fn() => $controller->update()),
        'DELETE' => requireAdminApiAuth(fn() => $controller->destroy()),
        default => http_response_code(405)
    };
}

/**
 * Garante autenticação para endpoints administrativos
 */
function requireAdminApiAuth(callable $handler): void
{
    if (!isAdminAuthenticated()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Sessão expirada. Faça login novamente no painel admin.'
        ]);
        return;
    }

    $handler();
}

/**
 * Verifica autenticação de admin via sessão
 */
function isAdminAuthenticated(): bool
{
    return !empty($_SESSION['admin_authenticated']);
}

/**
 * Retorna erro 404
 */
function handleNotFound(): void
{
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Recurso não encontrado'
    ]);
}

