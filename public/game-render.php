<?php
add_shortcode('rpg_form_game', function () {
  ob_start();

  $chapter_slug = isset($_GET['chapter']) ? sanitize_text_field($_GET['chapter']) : '';
  $ref_token = isset($_GET['ref']) ? sanitize_text_field($_GET['ref']) : '';

  if (!$chapter_slug || !$ref_token) {
    return '<p>❌ Invalid link. Missing chapter or ref.</p>';
  }

  $chapter = get_page_by_path($chapter_slug, OBJECT, 'rpg_chapter');
  if (!$chapter) {
    return '<p>❌ Chapter not found.</p>';
  }

  $whitelist_raw = get_post_meta($chapter->ID, 'rpg_ref_whitelist', true);
  $allowed_refs = array_map('trim', explode(',', $whitelist_raw));
  if (!in_array($ref_token, $allowed_refs)) {
    return '<p>⚠️ Token Ref tidak valid atau belum didaftarkan.</p>';
  }

  $questions = get_post_meta($chapter->ID, 'rpg_questions', true);
  if (!$questions || !is_array($questions)) {
    return '<p>⚠️ Tidak ada pertanyaan di chapter ini.</p>';
  }

  ?>
  <div id="rpg-game-container"></div>
  <script>
    const container = document.getElementById("rpg-game-container");
    const chapterSlug = <?= json_encode($chapter_slug); ?>;
    const ref = <?= json_encode($ref_token); ?>;
    const questions = <?= json_encode($questions); ?>;
    const secret = "Tombol2345tart!";
    const endpoint = "https://script.google.com/macros/s/AKfycbwJX_t7vdIX3ks3EPpvgnJZXobLzSRW9LW7FTu3W-vdQbGh-rrfkISo1sbxuMjtOGzq/exec"; // Ganti ini

    let current = 0;
    let answers = [];

    container.innerHTML = `
      <style>
        .dialog-box { background: #330044; color: white; padding: 20px; border-radius: 10px; font-family: sans-serif; max-width: 600px; margin: auto; }
        .question { font-size: 18px; margin-bottom: 10px; }
        .input-area { margin-bottom: 20px; }
        .button { padding: 10px 20px; background-color: #aa66ff; color: white; border: none; border-radius: 5px; cursor: pointer; }
      </style>
      <div class="dialog-box">
        <div id="question-text" class="question"></div>
        <div class="input-area" id="input-area"></div>
        <button class="button" onclick="next()">Next</button>
      </div>`;

    function renderQuestion() {
      const q = questions[current];
      document.getElementById("question-text").innerText = q.q;
      const area = document.getElementById("input-area");
      area.innerHTML = "";
      if (q.type === "text") {
        area.innerHTML = '<input type="text" id="answer" style="width:100%;padding:10px;font-size:16px;" />';
      } else {
        q.options.forEach(opt => {
          area.innerHTML += `<label><input type="radio" name="answer" value="${opt}" /> ${opt}</label><br>`;
        });
      }
    }

    window.next = function () {
      const q = questions[current];
      let answer;
      if (q.type === "text") {
        answer = document.getElementById("answer").value;
      } else {
        const selected = document.querySelector("input[name='answer']:checked");
        answer = selected ? selected.value : "";
      }

      if (!answer) return alert("Harap isi jawaban.");
      answers.push({ question: q.q, answer });
      current++;
      if (current < questions.length) return renderQuestion();
      submitAnswers();
    }

    function submitAnswers() {
      const timestamp = new Date().toISOString();
      answers.forEach(entry => {
        const formData = new URLSearchParams();
        formData.append("timestamp", timestamp);
        formData.append("chapter", chapterSlug);
        formData.append("ref", ref);
        formData.append("question", entry.question);
        formData.append("answer", entry.answer);
        formData.append("secret", secret);

        fetch(endpoint, {
          method: "POST",
          mode: "no-cors",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: formData
        });
      });
      const name = answers[0].answer || "teman";
      container.innerHTML = `<h2>Terima kasih, ${name}!</h2> Jawaban kamu sudah kami catat.`;
    }

    renderQuestion();
  </script>
  <?php

  return ob_get_clean();
});