<?php
/**
 * components.php - Componentes Reutilizáveis
 * 
 * Inclua este arquivo em qualquer view para usar funções de componente
 * 
 * Uso:
 *   require_once ROOT_PATH . '/app/components.php';
 *   echo renderButton('Clique aqui', 'primary', '/rota');
 */

/**
 * Renderiza um botão
 */
function renderButton($label, $variant = 'primary', $href = null, $icon = null, $attributes = [])
{
    $classes = "px-6 py-3 rounded-lg font-bold transition-all hover:scale-105 active:scale-95";
    
    $variants = [
        'primary' => 'bg-primary text-on-primary',
        'secondary' => 'bg-secondary-container text-on-secondary-container',
        'tertiary' => 'bg-tertiary text-white',
        'danger' => 'bg-error text-on-error',
        'ghost' => 'bg-transparent text-primary hover:bg-primary/10',
    ];

    $classes .= ' ' . ($variants[$variant] ?? $variants['primary']);

    $icon_html = $icon ? "<span class='material-symbols-outlined'>$icon</span>" : '';
    $attrs = implode(' ', array_map(fn($k, $v) => "$k='$v'", array_keys($attributes), array_values($attributes)));

    if ($href) {
        return "<a href='$href' class='$classes inline-flex items-center justify-center gap-2' $attrs>$icon_html $label</a>";
    }

    return "<button type='button' class='$classes flex items-center justify-center gap-2' $attrs>$icon_html $label</button>";
}

/**
 * Renderiza um card
 */
function renderCard($content, $title = null, $icon = null, $variant = 'default')
{
    $classes = "rounded-xl p-6 shadow-lg transition-all hover:scale-[1.02]";
    
    $variants = [
        'default' => 'bg-surface-container-lowest shadow-md',
        'primary' => 'bg-primary-container text-on-primary-container',
        'secondary' => 'bg-secondary-container text-on-secondary-container',
        'tertiary' => 'bg-tertiary-container text-on-tertiary-container',
        'error' => 'bg-error-container text-on-error-container',
    ];

    $classes .= ' ' . ($variants[$variant] ?? $variants['default']);

    $title_html = $title ? "<h3 class='text-xl font-bold mb-4 flex items-center gap-2'>
        {$icon ? "<span class='material-symbols-outlined'>$icon</span>" : ''}
        $title
    </h3>" : '';

    return "<div class='$classes'>
        $title_html
        $content
    </div>";
}

/**
 * Renderiza um input form
 */
function renderInput($name, $label = null, $type = 'text', $placeholder = null, $required = false, $value = null, $attributes = [])
{
    $id = "input_$name";
    $req = $required ? 'required' : '';
    $val = $value ? "value='$value'" : '';
    $ph = $placeholder ? "placeholder='$placeholder'" : '';
    $attrs = implode(' ', array_map(fn($k, $v) => "$k='$v'", array_keys($attributes), array_values($attributes)));

    $label_html = $label ? "<label for='$id' class='text-sm font-bold text-primary mb-2 block'>
        $label {$required ? '<span class="text-error">*</span>' : ''}
    </label>" : '';

    return "$label_html
    <input 
        type='$type' 
        id='$id' 
        name='$name' 
        class='w-full bg-surface-container-highest border border-outline rounded-lg focus:ring-2 focus:ring-primary focus:border-primary py-3 px-4 text-on-surface transition-all'
        $req $val $ph $attrs
    />";
}

/**
 * Renderiza um textarea
 */
function renderTextarea($name, $label = null, $placeholder = null, $required = false, $value = null, $rows = 4)
{
    $id = "textarea_$name";
    $req = $required ? 'required' : '';
    $val = $value ? htmlspecialchars($value) : '';
    $ph = $placeholder ? "placeholder='$placeholder'" : '';

    $label_html = $label ? "<label for='$id' class='text-sm font-bold text-primary mb-2 block'>
        $label {$required ? '<span class=\"text-error\">*</span>' : ''}
    </label>" : '';

    return "$label_html
    <textarea 
        id='$id' 
        name='$name' 
        rows='$rows'
        class='w-full bg-surface-container-highest border border-outline rounded-lg focus:ring-2 focus:ring-primary focus:border-primary py-3 px-4 text-on-surface transition-all resize-none'
        $req $ph>$val</textarea>";
}

/**
 * Renderiza um select
 */
function renderSelect($name, $options = [], $label = null, $selected = null, $required = false)
{
    $id = "select_$name";
    $req = $required ? 'required' : '';
    
    $label_html = $label ? "<label for='$id' class='text-sm font-bold text-primary mb-2 block'>
        $label {$required ? '<span class=\"text-error\">*</span>' : ''}
    </label>" : '';

    $options_html = '';
    foreach ($options as $value => $optLabel) {
        $sel = $selected == $value ? 'selected' : '';
        $options_html .= "<option value='$value' $sel>$optLabel</option>";
    }

    return "$label_html
    <select 
        id='$id' 
        name='$name'
        class='w-full bg-surface-container-highest border border-outline rounded-lg focus:ring-2 focus:ring-primary focus:border-primary py-3 px-4 text-on-surface transition-all'
        $req>
        <option value=''>-- Selecione --</option>
        $options_html
    </select>";
}

/**
 * Renderiza um alert/mensagem
 */
function renderAlert($message, $type = 'info', $dismissible = true, $icon = null)
{
    $icons = [
        'info' => 'info',
        'success' => 'check_circle',
        'warning' => 'warning',
        'error' => 'error',
    ];

    $colors = [
        'info' => 'bg-blue-100 border-blue-500 text-blue-700',
        'success' => 'bg-green-100 border-green-500 text-green-700',
        'warning' => 'bg-yellow-100 border-yellow-500 text-yellow-700',
        'error' => 'bg-red-100 border-red-500 text-red-700',
    ];

    $icon_element = $icon ?: $icons[$type] ?? 'info';
    $color_class = $colors[$type] ?? $colors['info'];
    $dismiss = $dismissible ? "<button onclick='this.parentElement.remove()' class='float-right text-lg leading-none'>×</button>" : '';

    return "<div class='border-l-4 $color_class p-4 mb-4 rounded-r-lg flex items-start gap-3'>
        <span class='material-symbols-outlined flex-shrink-0 mt-1'>$icon_element</span>
        <div class='flex-1'>
            $message
        </div>
        $dismiss
    </div>";
}

/**
 * Renderiza um loading spinner
 */
function renderLoader($message = 'Carregando...')
{
    return "<div class='flex flex-col items-center justify-center py-12 gap-4'>
        <div class='w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin'></div>
        <p class='text-on-surface-variant font-medium'>$message</p>
    </div>";
}

/**
 * Renderiza um badge/tag
 */
function renderBadge($label, $variant = 'primary', $size = 'md')
{
    $sizes = [
        'sm' => 'px-2 py-1 text-xs',
        'md' => 'px-3 py-2 text-sm',
        'lg' => 'px-4 py-3 text-base',
    ];

    $variants = [
        'primary' => 'bg-primary text-on-primary',
        'secondary' => 'bg-secondary-container text-on-secondary-container',
        'tertiary' => 'bg-tertiary-container text-on-tertiary-container',
        'success' => 'bg-green-100 text-green-700',
        'warning' => 'bg-yellow-100 text-yellow-700',
        'error' => 'bg-error-container text-on-error-container',
    ];

    $size_class = $sizes[$size] ?? $sizes['md'];
    $variant_class = $variants[$variant] ?? $variants['primary'];

    return "<span class='$size_class $variant_class rounded-full font-bold inline-block'>$label</span>";
}

/**
 * Renderiza um grid de cards
 */
function renderCardGrid($cards, $columns = 3)
{
    $col_class = "grid-cols-1 md:grid-cols-2 lg:grid-cols-$columns";
    $html = "<div class='grid $col_class gap-6'>";

    foreach ($cards as $card) {
        $html .= "<div class='bg-surface-container-lowest rounded-xl p-6 shadow-lg hover:scale-[1.02] transition-all'>";
        $html .= $card;
        $html .= "</div>";
    }

    $html .= "</div>";
    return $html;
}

/**
 * Renderiza breadcrumb
 */
function renderBreadcrumb($items = [])
{
    $html = "<nav class='flex items-center gap-2 text-sm mb-6'>";

    foreach ($items as $idx => $item) {
        if (isset($item['href'])) {
            $html .= "<a href='{$item['href']}' class='text-primary hover:underline'>{$item['label']}</a>";
        } else {
            $html .= "<span class='text-on-surface-variant'>{$item['label']}</span>";
        }

        if ($idx < count($items) - 1) {
            $html .= "<span class='text-on-surface-variant'>/</span>";
        }
    }

    $html .= "</nav>";
    return $html;
}

/**
 * Renderiza um table (simples)
 */
function renderTable($rows = [], $headers = [])
{
    $html = "<div class='overflow-x-auto rounded-lg shadow-md'><table class='w-full border-collapse'>";

    // Headers
    if (!empty($headers)) {
        $html .= "<thead class='bg-surface-container'>";
        $html .= "<tr>";
        foreach ($headers as $header) {
            $html .= "<th class='text-left p-4 font-bold text-on-surface-variant border-b border-outline-variant'>$header</th>";
        }
        $html .= "</tr>";
        $html .= "</thead>";
    }

    // Rows
    $html .= "<tbody>";
    foreach ($rows as $row) {
        $html .= "<tr class='border-b border-outline-variant hover:bg-surface-container-low transition-colors'>";
        foreach ($row as $cell) {
            $html .= "<td class='p-4 text-on-surface'>$cell</td>";
        }
        $html .= "</tr>";
    }
    $html .= "</tbody>";
    $html .= "</table></div>";

    return $html;
}

/**
 * Renderiza um modal (estrutura)
 */
function renderModal($id, $title, $content, $footer = null, $size = 'md')
{
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];

    $size_class = $sizes[$size] ?? $sizes['md'];
    $footer_html = $footer ? "<div class='border-t border-outline-variant p-6 flex justify-end gap-2'>$footer</div>" : '';

    return "
    <div id='$id' class='hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4'>
        <div class='$size_class bg-surface rounded-xl shadow-2xl overflow-hidden'>
            <div class='border-b border-outline-variant p-6 flex justify-between items-center'>
                <h2 class='text-2xl font-bold'>$title</h2>
                <button onclick=\"document.getElementById('$id').classList.add('hidden')\" class='text-on-surface-variant hover:text-on-surface'>
                    <span class='material-symbols-outlined'>close</span>
                </button>
            </div>
            <div class='p-6'>
                $content
            </div>
            $footer_html
        </div>
    </div>";
}
