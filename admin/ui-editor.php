<?php
// Register Custom Post Type
add_action('init', function () {
  register_post_type('rpg_chapter', [
    'labels' => [
      'name' => 'Interview Chapters',
      'singular_name' => 'Interview Chapter'
    ],
    'public' => true,
    'publicly_queryable' => true,
    'show_in_rest' => true,
    'rest_base' => 'rpg_chapter',
    'has_archive' => false,
    'rewrite' => ['slug' => 'chapter'],
    'menu_icon' => 'dashicons-welcome-write-blog',
    'supports' => ['title']
  ]);
});

// Add metabox
add_action('add_meta_boxes', function () {
  add_meta_box('rpg_questions_box', 'Daftar Pertanyaan', 'rpg_render_questions_box', 'rpg_chapter', 'normal', 'default');
});

function rpg_render_questions_box($post) {
  $questions = get_post_meta($post->ID, 'rpg_questions', true);
  $questions = is_array($questions) ? $questions : [];

  echo '<div id="rpg-questions"><table class="widefat"><thead><tr><th>Pertanyaan</th><th>Tipe</th><th>Opsi</th><th></th></tr></thead><tbody id="rpg-question-rows">';

  foreach ($questions as $i => $q) {
    $optionsStyle = ($q['type'] === 'choice') ? '' : 'style="display:none"';
    echo '<tr>
      <td><input name="rpg_q['.$i.'][q]" value="'.esc_attr($q['q']).'" style="width:100%"/></td>
      <td>
        <select name="rpg_q['.$i.'][type]" class="rpg-type">
          <option value="text" '.selected($q['type'], 'text', false).'>Text</option>
          <option value="choice" '.selected($q['type'], 'choice', false).'>Choice</option>
        </select>
      </td>
      <td><input name="rpg_q['.$i.'][options]" placeholder="Pisahkan dengan koma" value="'.(isset($q['options']) ? esc_attr(implode(',', $q['options'])) : '').'" '.$optionsStyle.'/></td>
      <td><button class="button rpg-remove-row">Hapus</button></td>
    </tr>';
  }

  echo '</tbody></table><p><button id="rpg-add-row" class="button">+ Tambah Pertanyaan</button></p></div>';

  // Whitelist Token Input
  echo '<hr><h4>Whitelist Ref Token</h4>';
  $token_list = get_post_meta($post->ID, 'rpg_ref_whitelist', true);
  echo '<textarea name="rpg_ref_whitelist" style="width:100%" rows="3" placeholder="contoh: abc123, xyz789">' . esc_textarea($token_list) . '</textarea>';
  echo '<p><small>Masukkan token ref yang diizinkan, pisahkan dengan koma (,)</small></p>';
}

// Save logic on manual submit
add_action('save_post_rpg_chapter', function($post_id) {
  if (isset($_POST['rpg_q'])) {
    $questions = array_map(function($q) {
      return [
        'q' => sanitize_text_field($q['q']),
        'type' => $q['type'],
        'options' => isset($q['options']) ? array_map('trim', explode(',', $q['options'])) : [],
      ];
    }, $_POST['rpg_q']);
    update_post_meta($post_id, 'rpg_questions', $questions);
  }

  if (isset($_POST['rpg_ref_whitelist']) && is_string($_POST['rpg_ref_whitelist'])) {
    $raw = sanitize_text_field($_POST['rpg_ref_whitelist']);
    update_post_meta($post_id, 'rpg_ref_whitelist', $raw);
  }
});