<?php
/**
 * Classe MusicController - HTTP Controller
 * 
 * Responsável por:
 * - Receber requisições HTTP/AJAX
 * - Orquestrar chamadas ao service
 * - Retornar respostas JSON padronizadas
 */

class MusicController
{
    /**
     * GET /api/musicas
     * Retorna lista de todas as músicas
     */
    public function list(): void
    {
        try {
            $musics = MusicService::getAllMusics();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => 'Músicas listadas com sucesso',
                'data' => array_map(fn($m) => $m->toArray(), $musics),
                'total' => count($musics),
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao listar músicas',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ]);
        }
    }

    /**
     * POST /api/musicas
     * Cria uma nova música
     */
    public function store(): void
    {
        try {
            // Obtém dados do body (JSON)
            $body = file_get_contents('php://input');
            $data = json_decode($body, true) ?? [];

            // Cria música via service
            $result = MusicService::createMusic($data);

            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao criar música',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ]);
        }
    }

    /**
     * PUT /api/musicas/{id}
     * Atualiza uma música existente
     */
    public function update(): void
    {
        try {
            // Obtém ID da URL
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $parts = array_filter(explode('/', $uri));
            $id = end($parts);

            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID inválido',
                ]);
                return;
            }

            // Obtém dados do body (JSON)
            $body = file_get_contents('php://input');
            $data = json_decode($body, true) ?? [];

            // Atualiza música via service
            $result = MusicService::updateMusic((int)$id, $data);

            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar música',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ]);
        }
    }

    /**
     * DELETE /api/musicas/{id}
     * Deleta uma música
     */
    public function destroy(): void
    {
        try {
            // Obtém ID da URL
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $parts = array_filter(explode('/', $uri));
            $id = end($parts);

            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID inválido',
                ]);
                return;
            }

            // Deleta música via service
            $result = MusicService::deleteMusic((int)$id);

            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao deletar música',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ]);
        }
    }

    /**
     * POST /api/musicas/import-csv/validate
     * Valida CSV sem persistir dados
     */
    public function validateCsv(): void
    {
        try {
            $file = $_FILES['csv_file'] ?? [];
            $result = MusicService::validateCsvUpload($file);

            http_response_code($result['success'] ? 200 : 400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao validar CSV.',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ]);
        }
    }

    /**
     * POST /api/musicas/import-csv
     * Importa CSV validado para o banco
     */
    public function importCsv(): void
    {
        try {
            $file = $_FILES['csv_file'] ?? [];
            $result = MusicService::importCsvUpload($file);

            http_response_code($result['success'] ? 200 : 400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao importar CSV.',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ]);
        }
    }
}

