function updateTime() {
  const now = new Date();
  const days = ['日', '月', '火', '水', '木', '金', '土'];
  const formattedDate = now.getFullYear() + '年' + (now.getMonth() + 1) + '月' + now.getDate() + '日（' + days[now.getDay()] + '）';
  const formattedTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
  document.getElementById('date-display').textContent = formattedDate;
  document.getElementById('time-display').textContent = formattedTime;
}
updateTime();
setInterval(updateTime, 60000); // 1分ごとに更新

