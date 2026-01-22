<?php
require_once __DIR__ . '/../core/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Lấy thông tin người dùng hiện tại để kiểm tra Key
$users = readJSON('users');
$currentUser = null;
foreach ($users as $user) {
    if ($user['id'] === $_SESSION['user_id']) {
        $currentUser = $user;
        break;
    }
}

$keys = readJSON('keys');
$activeKey = null;
foreach ($keys as $key) {
    if ($key['user_id'] === $currentUser['id']) {
        $created_at = strtotime($key['created_at']);
        $package = $key['package_name'];
        $duration = 0;
        if (strpos($package, 'Giờ') !== false) {
            $hours = (int)filter_var($package, FILTER_SANITIZE_NUMBER_INT);
            $duration = $hours * 3600;
        } elseif (strpos($package, 'Ngày') !== false) {
            $days = (int)filter_var($package, FILTER_SANITIZE_NUMBER_INT);
            $duration = $days * 86400;
        }
        $expiry_time = $created_at + $duration;
        if (time() < $expiry_time) {
            $activeKey = $key;
            break;
        }
    }
}

if (!$activeKey) {
    header('Location: taixiu-select.php?error=no_active_key');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>Dự Đoán Lẩu Cua 79</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #0f172a;
      color: #f8fafc;
      font-family: 'Plus Jakarta Sans', sans-serif;
      margin: 0;
      padding: 10px;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-image: 
        radial-gradient(at 0% 0%, rgba(251, 191, 36, 0.1) 0px, transparent 50%),
        radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.1) 0px, transparent 50%),
        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .wrapper {
      max-width: 450px;
      width: 100%;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      padding: 30px;
      border-radius: 32px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      border: 1px solid rgba(255, 255, 255, 0.15);
      position: relative;
      overflow: hidden;
    }

    .wrapper::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle at center, rgba(234, 179, 8, 0.05) 0%, transparent 70%);
      pointer-events: none;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      color: #94a3b8;
      text-decoration: none;
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 25px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      padding: 8px 16px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .back-btn:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.08);
      transform: translateX(-4px);
      border-color: rgba(255, 255, 255, 0.1);
    }

    h2 {
      text-align: center;
      background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 30px;
      font-weight: 800;
      font-size: 24px;
      letter-spacing: -0.5px;
    }

    .prediction-container {
      background: rgba(255, 255, 255, 0.03);
      border-radius: 24px;
      padding: 20px;
      border: 1px solid rgba(255, 255, 255, 0.05);
      margin-bottom: 25px;
      text-align: center;
    }

    .circle {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      border: 2px solid rgba(255, 255, 255, 0.1);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      margin: 0 auto 20px;
      background: radial-gradient(circle at center, rgba(30, 41, 59, 0.8) 0%, #000 100%);
      position: relative;
      transition: all 0.5s ease;
    }

    .circle::before {
      content: '';
      position: absolute;
      inset: -8px;
      border-radius: 50%;
      background: conic-gradient(from 0deg, transparent, rgba(234, 179, 8, 0.4), transparent);
      animation: rotate 4s linear infinite;
    }

    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    #prediction {
      font-size: 42px;
      font-weight: 900;
      color: #fff;
      text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
      z-index: 1;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 16px;
      background: rgba(34, 197, 94, 0.1);
      border: 1px solid rgba(34, 197, 94, 0.2);
      border-radius: 100px;
      margin-bottom: 10px;
    }

    .status-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #22c55e;
      box-shadow: 0 0 10px #22c55e;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.2); opacity: 0.5; }
      100% { transform: scale(1); opacity: 1; }
    }

    .status-text {
      font-size: 11px;
      font-weight: 800;
      color: #22c55e;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .time-display {
      font-family: monospace;
      font-size: 14px;
      color: #64748b;
      font-weight: 600;
    }

    .history-section {
      background: rgba(15, 23, 42, 0.4);
      border-radius: 24px;
      padding: 20px;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .section-title {
      font-size: 11px;
      font-weight: 800;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 2px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section-title::after {
      content: '';
      flex: 1;
      height: 1px;
      background: rgba(255, 255, 255, 0.05);
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px;
    }

    th {
      text-align: left;
      font-size: 10px;
      color: #64748b;
      font-weight: 700;
      text-transform: uppercase;
      padding: 0 10px 10px;
    }

    td {
      padding: 12px 10px;
      background: rgba(255, 255, 255, 0.02);
      font-size: 12px;
    }

    td:first-child {
      border-radius: 12px 0 0 12px;
      font-family: monospace;
      font-weight: 700;
      color: #94a3b8;
    }

    td:last-child {
      border-radius: 0 12px 12px 0;
      color: #475569;
    }

    .badge-prediction {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 8px;
      font-weight: 800;
      font-size: 10px;
      text-transform: uppercase;
    }

    .badge-tai { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
    .badge-xiu { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    
    .eval-win { color: #22c55e; font-weight: 800; font-size: 11px; }
    .eval-lose { color: #ef4444; font-weight: 800; font-size: 11px; }
    .eval-pending { color: #64748b; font-style: italic; }

    ::-webkit-scrollbar {
      width: 4px;
    }
    ::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <a href="taixiu-select.php" class="back-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      Trở lại
    </a>
    
    <h2>DỰ ĐOÁN LẨU CUA 79</h2>

    <div class="prediction-container">
      <div class="circle">
        <div id="prediction">--</div>
      </div>
      
      <div class="status-badge">
        <div class="status-dot"></div>
        <div class="status-text" id="status">Kết nối...</div>
      </div>
      
      <div class="time-display" id="clock">00:00:00</div>
    </div>

    <div class="history-section">
      <div class="section-title">Lịch sử phân tích</div>
      <div style="max-height: 280px; overflow-y: auto; padding-right: 5px;">
        <table>
          <thead>
            <tr>
              <th>Phiên</th>
              <th>Dự đoán</th>
              <th>Kết quả</th>
              <th>Đánh giá</th>
            </tr>
          </thead>
          <tbody id="history">
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    const apiURL = "api/get-prediction.php?game=lau-cua-79";
    let historyData = [];

    function formatTime() {
      const now = new Date();
      return now.getHours().toString().padStart(2, '0') + ':' + 
             now.getMinutes().toString().padStart(2, '0') + ':' + 
             now.getSeconds().toString().padStart(2, '0');
    }

    function addRow(session, prediction, result) {
      const row = document.createElement('tr');
      
      let predBadge = '';
      if (prediction === 'Tài') predBadge = '<span class="badge-prediction badge-tai">Tài</span>';
      else if (prediction === 'Xỉu') predBadge = '<span class="badge-prediction badge-xiu">Xỉu</span>';
      else predBadge = '<span class="eval-pending">--</span>';

      let resBadge = '';
      if (result === 'Tài') resBadge = '<span class="badge-prediction badge-tai">Tài</span>';
      else if (result === 'Xỉu') resBadge = '<span class="badge-prediction badge-xiu">Xỉu</span>';
      else resBadge = '<span class="eval-pending">--</span>';

      let eval = '';
      if (result && result !== '--') {
        if (prediction === result) eval = '<span class="eval-win">WIN</span>';
        else if (prediction && prediction !== '--') eval = '<span class="eval-lose">LOSE</span>';
        else eval = '<span class="eval-pending">--</span>';
      } else {
        eval = '<span class="eval-pending">Chờ...</span>';
      }

      row.innerHTML = `
        <td>#${session.toString().slice(-4)}</td>
        <td>${predBadge}</td>
        <td>${resBadge}</td>
        <td>${eval}</td>
      `;

      const table = document.getElementById("history");
      table.prepend(row);
      if (table.rows.length > 15) table.deleteRow(15);
    }

    async function fetchData() {
      try {
        const res = await fetch(apiURL);
        const data = await res.json();

        const predEl = document.getElementById("prediction");
        predEl.innerText = data.prediction || "--";
        
        if (data.prediction === 'Tài') predEl.style.color = '#fbbf24';
        else if (data.prediction === 'Xỉu') predEl.style.color = '#ef4444';
        else predEl.style.color = '#fff';

        document.getElementById("status").innerText = "Đã đồng bộ";

        if (data.current_result && data.current_result !== '--' && !historyData.includes(data.current_session)) {
          historyData.push(data.current_session);
          addRow(data.current_session, data.prediction || "--", data.current_result);
        }
      } catch (e) {
        document.getElementById("status").innerText = "Lỗi hệ thống";
        document.getElementById("status").parentElement.style.borderColor = "rgba(239, 68, 68, 0.2)";
        document.querySelector(".status-dot").style.backgroundColor = "#ef4444";
      }
    }

    function updateClock() {
      document.getElementById("clock").innerText = formatTime();
    }

    setInterval(updateClock, 1000);
    updateClock();
    setInterval(fetchData, 1000);
    fetchData();
  </script>
</body>
</html>
