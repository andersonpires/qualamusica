<?php
/**
 * Classe Music - Model/Entity
 * 
 * Responsável por:
 * - Representar uma música
 * - Encapsular seus dados
 * - Validações simples de entidade
 */

class Music
{
    public ?int $id = null;
    public string $nome;
    public string $cantor;
    public string $autor;
    public string $link_karaoke;
    public string $link_clipe;
    public int $ano;
    public ?string $criado_em = null;
    public ?string $atualizado_em = null;

    /**
     * Construtor
     */
    public function __construct(
        string $nome = '',
        string $cantor = '',
        string $autor = '',
        string $link_karaoke = '',
        string $link_clipe = '',
        int $ano = 0,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->cantor = $cantor;
        $this->autor = $autor;
        $this->link_karaoke = $link_karaoke;
        $this->link_clipe = $link_clipe;
        $this->ano = $ano;
    }

    /**
     * Converte para array (útil para JSON)
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cantor' => $this->cantor,
            'autor' => $this->autor,
            'link_karaoke' => $this->link_karaoke,
            'link_clipe' => $this->link_clipe,
            'ano' => $this->ano,
            'criado_em' => $this->criado_em,
            'atualizado_em' => $this->atualizado_em,
        ];
    }

    /**
     * Valida se a música tem os campos obrigatórios
     * 
     * @return array Array de erros (vazio se válido)
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->nome))) {
            $errors['nome'] = 'Nome da música é obrigatório';
        }

        if (empty(trim($this->cantor))) {
            $errors['cantor'] = 'Cantor/Artista é obrigatório';
        }

        if (empty(trim($this->autor))) {
            $errors['autor'] = 'Autor/Compositor é obrigatório';
        }

        if (empty(trim($this->link_karaoke))) {
            $errors['link_karaoke'] = 'Link do karaokê é obrigatório';
        }

        if (empty(trim($this->link_clipe))) {
            $errors['link_clipe'] = 'Link do clipe é obrigatório';
        }

        if ($this->ano < 1800 || $this->ano > date('Y')) {
            $errors['ano'] = 'Ano inválido';
        }

        return $errors;
    }

    /**
     * Obtém apenas dados públicos para exibição em "Tocando"
     * (sem revelar a resposta)
     */
    public function toArrayForPlaying(): array
    {
        return [
            'id' => $this->id,
            'link_karaoke' => $this->link_karaoke,
        ];
    }

    /**
     * Obtém dados completos para exibição em "Revelar"
     */
    public function toArrayForReveal(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cantor' => $this->cantor,
            'autor' => $this->autor,
            'link_clipe' => $this->link_clipe,
            'ano' => $this->ano,
        ];
    }
}
