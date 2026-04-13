<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Dự đoán Chẵn Lẻ - LC79 VIP</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    html, body { margin: 0; padding: 0; overflow: hidden; font-family: Arial, sans-serif; }

    iframe { width: 100vw; height: 100vh; border: none; display: block; }

    /* Robot container */
    #robotContainer {
      position: fixed;
      bottom: 30px;
      right: 30px;
      display: flex;
      flex-direction: row;
      align-items: center;
      z-index: 9999;
      cursor: grab;
      user-select: none;
      -webkit-user-select: none;
    }

    #robotContainer.dragging { cursor: grabbing; }

    .robotIconWrapper {
      position: relative;
      width: 90px;
      height: 90px;
      flex-shrink: 0;
    }

    #robotIcon {
      width: 100%;
      height: 100%;
      animation: nhay 1.5s ease-in-out infinite;
      display: block;
      pointer-events: none;
      -webkit-user-drag: none;
      user-drag: none;
    }

    #closeBtn {
      position: absolute;
      top: -8px;
      right: -8px;
      width: 24px;
      height: 24px;
      background: #ff4444;
      color: #fff;
      font-size: 14px;
      font-weight: bold;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      pointer-events: auto;
      z-index: 10000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.3);
      transition: all 0.2s;
    }
    #closeBtn:hover {
      background: #ff6666;
      transform: scale(1.05);
    }

    @keyframes nhay {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }

    /* Bubble */
    #robotBubble {
      position: relative;
      margin-left: 15px;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 24px;
      padding: 12px 20px;
      min-width: 220px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    #robotBubble::before {
      content: "";
      position: absolute;
      left: -12px;
      top: 50%;
      transform: translateY(-50%);
      border-width: 10px 12px 10px 0;
      border-style: solid;
      border-color: transparent rgba(0, 0, 0, 0.5) transparent transparent;
    }

    .bubble-label {
      font-size: 10px;
      color: rgba(255, 255, 255, 0.6);
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-bottom: 6px;
      font-weight: 500;
    }

    #phienText {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 5px;
      font-weight: 500;
    }

    #duDoanText {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 6px;
      letter-spacing: 1px;
    }

    #duDoanText.chan {
      color: #00ff88;
      text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
    }

    #duDoanText.le {
      color: #ff4444;
      text-shadow: 0 0 10px rgba(255, 68, 68, 0.5);
    }

    #xucXacText {
      font-size: 14px;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 500;
      margin-top: 8px;
      padding-top: 5px;
      border-top: 1px solid rgba(255,255,255,0.2);
    }

    #cuaText {
      font-size: 12px;
      color: #ffd700;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .dot-pulse {
      display: inline-flex;
      gap: 5px;
      align-items: center;
      height: 30px;
    }
    .dot-pulse span {
      width: 8px;
      height: 8px;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 50%;
      animation: pulse 1.2s ease-in-out infinite;
    }
    .dot-pulse span:nth-child(2) { animation-delay: 0.2s; }
    .dot-pulse span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes pulse {
      0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
      40% { transform: scale(1.2); opacity: 1; }
    }
  </style>
</head>
<body>

<iframe id="gameFrame" src="https://lc79b.bet/"></iframe>

<div id="robotContainer">
  <div class="robotIconWrapper">
    <img id="robotIcon"
      src="https://i.postimg.cc/Z5K6LT20/049249-C3-2538-401-A-A066-40-EAA5-DA88-A7.png"
      draggable="false">
    <div id="closeBtn">×</div>
  </div>

  <div id="robotBubble">
    <div class="bubble-label">🤖 AI DỰ ĐOÁN</div>
    <div id="phienText"></div>
    <div id="duDoanText"><div class="dot-pulse"><span></span><span></span><span></span></div></div>
    <div id="cuaText"></div>
    <div id="xucXacText"></div>
  </div>
</div>

<script>
  const robot = document.getElementById("robotContainer");
  const frame = document.getElementById("gameFrame");
  const close = document.getElementById("closeBtn");

  let lastPhien = "";
  let isFetching = false;
  let isFirstLoad = true;

  // Kéo thả robot
  let dragging = false;
  let startX, startY, origLeft, origTop;

  function startDrag(cx, cy) {
    dragging = true;
    robot.classList.add("dragging");
    startX   = cx;
    startY   = cy;
    origLeft = robot.offsetLeft;
    origTop  = robot.offsetTop;
    frame.style.pointerEvents = "none";
  }

  function moveDrag(cx, cy) {
    if (!dragging) return;
    robot.style.left = (origLeft + cx - startX) + "px";
    robot.style.top  = (origTop  + cy - startY) + "px";
    robot.style.bottom = "auto";
    robot.style.right = "auto";
  }

  function endDrag() {
    if (!dragging) return;
    dragging = false;
    robot.classList.remove("dragging");
    frame.style.pointerEvents = "auto";
  }

  robot.addEventListener("mousedown", e => {
    if (e.target === close) return;
    e.preventDefault();
    startDrag(e.clientX, e.clientY);
  });
  document.addEventListener("mousemove", e => moveDrag(e.clientX, e.clientY));
  document.addEventListener("mouseup", endDrag);

  robot.addEventListener("touchstart", e => {
    if (e.target === close) return;
    const t = e.touches[0];
    startDrag(t.clientX, t.clientY);
  }, { passive: true });
  document.addEventListener("touchmove", e => {
    const t = e.touches[0];
    moveDrag(t.clientX, t.clientY);
  }, { passive: true });
  document.addEventListener("touchend", endDrag);

  close.addEventListener("click", e => {
    e.stopPropagation();
    robot.style.display = "none";
  });

  function showLoading() {
    document.getElementById("phienText").innerHTML = "🎲 Đang cập nhật...";
    document.getElementById("duDoanText").innerHTML = '<div class="dot-pulse"><span></span><span></span><span></span></div>';
    document.getElementById("cuaText").innerHTML = "";
    document.getElementById("xucXacText").innerHTML = "";
  }

  function formatXucXac(xucXac) {
    if (!xucXac || !Array.isArray(xucXac)) return "";
    const icons = xucXac.map(x => x === 'trang' ? '⚪ Trắng' : '🔴 Đỏ');
    return icons.join(' | ');
  }

  function getCuaDatText(cuaDat, soTrang, soDo) {
    switch(cuaDat) {
      case '4_do': return "🎯 CỬA: 4 ĐỎ";
      case '4_trang': return "🎯 CỬA: 4 TRẮNG";
      case '1_do_3_trang': return "🎯 CỬA: 1 ĐỎ - 3 TRẮNG";
      case '1_trang_3_do': return "🎯 CỬA: 1 TRẮNG - 3 ĐỎ";
      default: return `🎯 ${soTrang} TRẮNG - ${soDo} ĐỎ`;
    }
  }

  function showResult(phien, duDoan, xucXac, cuaDat, soTrang, soDo) {
    document.getElementById("phienText").innerHTML = `🎲 Phiên #${phien}`;
    
    const duDoanElement = document.getElementById("duDoanText");
    const duDoanText = duDoan === 'chan' ? 'CHẴN' : 'LẺ';
    duDoanElement.innerText = duDoanText;
    
    if (duDoan === 'chan') {
      duDoanElement.className = "chan";
    } else {
      duDoanElement.className = "le";
    }
    
    document.getElementById("cuaText").innerHTML = getCuaDatText(cuaDat, soTrang, soDo);
    document.getElementById("xucXacText").innerHTML = `🎲 ${formatXucXac(xucXac)}`;
  }

  async function fetchData() {
    if (isFetching) return;
    isFetching = true;
    try {
      if (isFirstLoad) showLoading();
      isFirstLoad = false;

      const res = await fetch('api.php', { 
        cache: "no-store"
      });
      
      if (!res.ok) throw new Error('Network error');
      const data = await res.json();

      if (data.success) {
        const phien = data.phien_hien_tai;
        const duDoan = data.du_doan;
        const xucXac = data.du_doan_xuc_xac;
        const cuaDat = data.cua_dat;
        const soTrang = data.so_trang;
        const soDo = data.so_do;

        if (phien && String(phien) !== lastPhien) {
          lastPhien = String(phien);
          showLoading();
          setTimeout(() => showResult(phien, duDoan, xucXac, cuaDat, soTrang, soDo), 500);
        } else if (phien && lastPhien === String(phien)) {
          const duDoanDiv = document.getElementById("duDoanText");
          if (duDoanDiv && !duDoanDiv.querySelector(".dot-pulse")) {
            showResult(phien, duDoan, xucXac, cuaDat, soTrang, soDo);
          }
        }
      } else {
        throw new Error(data.message || 'Invalid data');
      }
    } catch (e) {
      console.error("Fetch error:", e);
      document.getElementById("phienText").innerHTML = "⚠️ Lỗi kết nối";
      document.getElementById("duDoanText").innerHTML = "⚠️ Đang thử lại...";
      document.getElementById("cuaText").innerHTML = "";
      document.getElementById("xucXacText").innerHTML = "";
    }
    isFetching = false;
  }

  // Khởi tạo
  showLoading();
  setInterval(fetchData, 3000);
  fetchData();
</script>
</body>
</html>