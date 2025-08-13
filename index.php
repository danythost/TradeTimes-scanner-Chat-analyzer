<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AiAssistant</title>
      <style>
  body {
    font-family: 'Segoe UI', sans-serif;
    padding: 40px;
    max-width: 800px;
    margin: auto;
    background-color: #f9f9f9;
  }

  h2 {
    margin-bottom: 20px;
  }

  input[type="file"], input[type="text"], textarea, button {
    margin: 15px 0;
    padding: 12px;
    width: 100%;
    box-sizing: border-box;
    border-radius: 8px;
    border: 1px solid #ccc;
  }

  .custom-file-label {
  display: inline-block;
  padding: 12px 20px;
  background-color: #007bff;
  color: white;
  border-radius: 30px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s ease;
}

.custom-file-label:hover {
  background-color: #0056b3;
}

.file-name {
  display: inline-block;
  margin-left: 10px;
  font-size: 14px;
  color: #333;
}


  .bubble-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 10px 0 20px 0;
  }

  .bubble-option {
  padding: 10px 20px;
  border-radius: 30px;
  background-color: #e0e0e0;
  cursor: pointer;
  user-select: none;
  transition: 0.2s ease;
  font-size: 14px;
  border: 1px solid transparent;
}

.bubble-option:hover {
  background-color:rgb(100, 241, 119);
  box-shadow: 0 2px 6px rgba(92, 55, 228, 0.15);
  transform: scale(1.05);
  border: 1px solid #ccc;
}

.bubble-option.selected {
    background: #007bff;
    color: white;
  }

.bubble-option.active {
  background-color: #007bff;
  color: white;
  box-shadow: 0 3px 10px rgba(0, 123, 255, 0.3);
  border: 1px solid #007bff;
}

  #response {
    margin-top: 25px;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
  }

  label {
    font-weight: 600;
    margin-top: 15px;
    display: block;
  }

  button[type="submit"] {
    background-color: #007bff;
    color: white;
    font-weight: bold;
    border: none;
    transition: 0.3s;
  }

  button[type="submit"]:hover {
    background-color: #0056b3;
  }

  .form-step {
    display: none;
    transition: all 0.4s ease;
  }

  .form-step.active {
    display: block;
    opacity: 1;
  }

#responseText h2, #responseText h3 {
  margin-top: 1em;
  font-weight: bold;
}
#responseText p {
  margin-bottom: 0.8em;
}
#responseText ul {
  padding-left: 1.2em;
  list-style-type: disc;
}

</style>
  </head>
  <body>
  <div class="container">
        <div class="row">
            <div class="header" align="center">
            <h2>Tradetimes ProChart Analysis</h2>
            </div>

<form id="aiForm" enctype="multipart/form-data">
<div id="response">
  <strong>AI's Analysis:</strong><br>
  <span id="responseText">upload Image and Continue...</span>
</div>
  <!-- Upload + Preview + Prompt -->
  <div class="form-step active">
    <label for="imageInput" class="custom-file-label">📁 Upload Chart Image</label>
    <input type="file" name="image" id="imageInput" accept="image/*" required hidden>
    <span id="fileName" class="file-name"></span>
    <img id="preview" style="max-width: 100%; margin-top: 15px; display: none;" />
    <button type="button" id="continueBtn" style="display:none;">Continue</button>
  </div>
            

<!-- User Experience Level -->
  <div class="form-step">
    <label>Experience Level:</label>
    <div class="bubble-group" data-name="level">
      <div class="bubble-option">Beginner</div>
      <div class="bubble-option">Advance</div>
    </div>
    <input type="hidden" name="level" />
  </div>


  <!-- Currency Pair -->
  <div class="form-step">
    <label>Currency Pair:</label>
    <div class="bubble-group" data-name="pair">
      <div class="bubble-option">EUR/USD</div>
      <div class="bubble-option">USD/JPY</div>
      <div class="bubble-option">GBP/USD</div>
      <div class="bubble-option">BTC/USD</div>
    </div>
    <input type="hidden" name="pair" />
  </div>

  <!-- Timeframe -->
  <div class="form-step">
    <label>Timeframe:</label>
    <div class="bubble-group" data-name="timeframe">
      <div class="bubble-option">1m</div>
      <div class="bubble-option">5m</div>
      <div class="bubble-option">15m</div>
      <div class="bubble-option">1h</div>
      <div class="bubble-option">1d</div>
    </div>
    <input type="hidden" name="timeframe" />
  </div>

  <!-- Submit Button -->
  <div class="form-step">
    <button type="submit">📊 Analyze</button>
  </div>

</form>


    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
  const steps = document.querySelectorAll('.form-step');
  const form = document.getElementById('aiForm');
  const responseText = document.getElementById('responseText');
  let currentStep = 0;

  function goToNextStep() {
    if (currentStep < steps.length - 1) {
      steps[currentStep].classList.remove('active');
      currentStep++;
      steps[currentStep].classList.add('active');
    }
  }

  // Continue button (image step)
  document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    const preview = document.getElementById('preview');
    const fileName = document.getElementById('fileName');
    const continueBtn = document.getElementById('continueBtn');

    if (file) {
      preview.src = URL.createObjectURL(file);
      preview.style.display = 'block';
      fileName.textContent = `Selected file: ${file.name}`;
      continueBtn.style.display = 'inline-block';
    } else {
      preview.style.display = 'none';
      fileName.textContent = '';
      continueBtn.style.display = 'none';
    }
  });

  document.getElementById('continueBtn').addEventListener('click', () => {
    goToNextStep();
  });

  // question input blur + image selected
  document.getElementById('questionInput')?.addEventListener('blur', () => {
    const img = document.getElementById('imageInput').files.length;
    const q = document.getElementById('questionInput').value.trim();
    if (img && q !== "") {
      goToNextStep();
    }
  });

  // bubble option logic (includes Experience Level and others)
  document.querySelectorAll('.bubble-group').forEach(group => {
    const name = group.dataset.name;
    const step = group.closest('.form-step');
    const hiddenInput = step.querySelector(`input[name="${name}"]`);

    group.querySelectorAll('.bubble-option').forEach(option => {
      option.addEventListener('click', () => {
        group.querySelectorAll('.bubble-option').forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        hiddenInput.value = option.textContent;
        goToNextStep(); // This ensures it goes to the next step
      });
    });
  });



form.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  responseText.innerHTML = '';
  responseText.textContent = 'Analyzing...';

  const formData = new FormData(form);

  try {
    const res = await fetch('analyze.php', {
      method: 'POST',
      body: formData
    });

    const text = await res.text();
    let data;

    try {
      data = JSON.parse(text);
      console.log("✅ Parsed JSON:", data);
    } catch (err) {
      responseText.innerHTML = `<div style="color:red;">❌ Could not parse response. Raw output:</div><pre>${text}</pre>`;
      return;
    }

    //  CHART VALIDATION HANDLING 
    if (data.currency_pair && data.timeframe && data.is_chart_valid !== undefined) {
      if (data.is_chart_valid === false) {
        responseText.innerHTML = `
          <div style="color:orange; border:1px solid #ffc107; padding:10px; border-radius:8px;">
            ⚠️ <strong>Invalid chart image</strong><br/>
            <strong>Pair:</strong> ${data.currency_pair}<br/>
            <strong>Timeframe:</strong> ${data.timeframe}<br/>
            <strong>Error:</strong> ${data.error || 'No explanation provided.'}
          </div>
        `;
        return;
      }

      //
      responseText.innerHTML = `
        <div style="border:1px solid #ccc; padding:15px; border-radius:8px;">
          <h3>📊 Trade Setup: ${data.experience_level} user, ${data.currency_pair} (${data.timeframe})</h3>
          <ul style="list-style:none; padding:0;">
            <li><strong>📈 Direction:</strong> ${data.trade_direction || 'N/A'}</li>
            <li><strong>🔑 Entry:</strong> ${data.entry_point || 'N/A'}</li>
            <li><strong>🛡️ Stop Loss:</strong> ${data.stop_loss || 'N/A'}</li>
            <li><strong>🎯 Take Profit:</strong> ${data.take_profit || 'N/A'}</li>
            <li><strong>🧠 Pattern:</strong> ${data.identified_pattern || 'N/A'}</li>
            <li><strong>📊 Success Rate:</strong> ${data.estimated_success_rate || 'N/A'}</li>
          </ul>
          <h4>📝 Summary:</h4>
          <div>${data.summary_explanation || 'No summary provided.'}</div>
          <h4>📌 Visual Instructions:</h4>
          <div>${data.visual_annotation_instructions || 'None'}</div>
          ${data.annotated_chart_url 
            ? `<div><img src="${data.annotated_chart_url}" alt="Annotated Chart" style="max-width:100%; margin-top:10px;" /></div>` 
            : ''}
        </div>
      `;

    } else {
      // GPT output (Markdown-style)
      const reply = data.reply || data.error || 'No structured response from AI.';
      responseText.innerHTML = marked.parse(reply);
    }

  } catch (err) {
    responseText.textContent = 'Error: ' + err.message;
    console.error('❌ Fetch error:', err);
  }
});

</script>


 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
