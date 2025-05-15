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
  ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const tbody = document.getElementById('rpg-question-rows');
      const form = document.querySelector('form');

      function bindEvents(select) {
        toggleOptionsField(select);
        select.addEventListener('change', () => toggleOptionsField(select));
      }

      document.getElementById('rpg-add-row').onclick = () => {
        const i = tbody.children.length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><input name="rpg_q[${i}][q]" style="width:100%" /></td>
          <td>
            <select name="rpg_q[${i}][type]" class="rpg-type">
              <option value="text">Text</option>
              <option value="choice">Choice</option>
            </select>
          </td>
          <td><input name="rpg_q[${i}][options]" placeholder="Pisahkan dengan koma" style="display:none" /></td>
          <td><button class="button rpg-remove-row">Hapus</button></td>
        `;
        tbody.appendChild(tr);

        // Trigger unsaved changes warning
        window.onbeforeunload = () => 'Perubahan belum disimpan.';

        bindEvents(tr.querySelector('select'));
      };

      tbody.addEventListener('click', e => {
        if (e.target.classList.contains('rpg-remove-row')) {
          e.target.closest('tr').remove();
        }
      });

      document.querySelectorAll('#rpg-question-rows select').forEach(select => bindEvents(select));

      if (form) {
        form.addEventListener('submit', () => {
          window.onbeforeunload = null;
        });
      }

      function toggleOptionsField(select) {
        const row = select.closest('tr');
        const input = row.querySelector('input[name*="[options]"]');
        if (select.value === 'choice') {
          input.style.display = 'block';
        } else {
          input.style.display = 'none';
          input.value = '';
        }
      }
    });
  </script>
  <?php

  $slug = get_post_field('post_name', $post);
  $sample_link = site_url('/your-form-page-slug/?chapter=' . $slug . '&ref=user-xyz');
  echo '<hr><p><strong>Generate Link:</strong></p>';
  echo '<code id="rpg-copy-link">'.$sample_link.'</code><br>';
  echo '<button class="button" onclick="navigator.clipboard.writeText(document.getElementById(\'rpg-copy-link\').innerText)">📋 Copy Link</button>';
  echo '<p><em>Link ini bisa dibagikan ke player untuk mengisi form.</em></p>';
  echo '<p><em>Contoh: <code>https://example.com/your-form-page-slug/?chapter=chapter-slug&ref=user-xyz</code></em></p>';
  echo '<p><em>Ganti <code>your-form-page-slug</code> dengan slug halaman form yang sesuai.</em></p>';
}

add_action('save_post_rpg_chapter', function ($post_id) {
  if (isset($_POST['rpg_q'])) {
    $qs = array_map(function ($q) {
      $q['options'] = isset($q['options']) ? array_map('trim', explode(',', $q['options'])) : [];
      return $q;
    }, $_POST['rpg_q']);
    update_post_meta($post_id, 'rpg_questions', $qs);
  }
});

add_action('add_meta_boxes', function () {
  add_meta_box('rpg_ref_whitelist', 'Whitelist Ref (Token Akses)', 'rpg_ref_list_box', 'rpg_chapter', 'side');
});

function rpg_ref_list_box($post) {
  $tokens = get_post_meta($post->ID, 'rpg_ref_whitelist', true);
  $tokens = is_array($tokens) ? implode("\n", $tokens) : "";
  echo '<textarea name="rpg_ref_whitelist" style="width:100%;height:100px;" placeholder="Satu token per baris...">'.$tokens.'</textarea>';
  echo '<p><em>Token ini wajib dimiliki player agar bisa submit jawaban.</em></p>';
}

add_action('save_post_rpg_chapter', function ($post_id) {
  if (isset($_POST['rpg_ref_whitelist'])) {
    $lines = explode("\n", $_POST['rpg_ref_whitelist']);
    $tokens = array_filter(array_map('trim', $lines));
    update_post_meta($post_id, 'rpg_ref_whitelist', $tokens);
  }
});
