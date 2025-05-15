<?php
add_shortcode('rpg_form_game', function () {
  ob_start();
  ?>
  <div id="rpg-game-container"></div>
  <script>
    const container = document.getElementById("rpg-game-container");
    const urlParams = new URLSearchParams(window.location.search);
    const chapterSlug = urlParams.get("chapter") || "";
    const ref = urlParams.get("ref") || "anonymous";
    const secret = "Tombol#2345tart!";
    const endpoint = "https://script.google.com/macros/s/AKfyc.../exec"; // Ganti ini

    fetch(`/wp-json/wp/v2/rpg_chapter?slug=${chapterSlug}`)
      .then(res => res.json())
      .then(data => {
        if (!data[0]) return container.innerHTML = "<p>Chapter tidak ditemukan.</p>";
        const postId = data[0].id;
        fetch(`/wp-json/wp/v2/rpg_chapter/${postId}`)
          .then(res => res.json())
          .then(post => {
            const whitelist = post.meta.rpg_ref_whitelist || [];
            if (whitelist.length > 0 && !whitelist.includes(ref)) {
                container.innerHTML = "<p>⚠️ Token Ref tidak valid atau belum didaftarkan.</p>";
                return;
            }
            renderGame(post.meta.rpg_questions || []);
            });
      });

    function renderGame(questions) {
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
    }
  </script>
  <?php
  return ob_get_clean();
});
