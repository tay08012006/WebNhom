<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Lecture Notes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <script>
    // Cấu hình Tailwind để hỗ trợ toggle dark mode bằng class
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
  <style>
    .note-item {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .note-item:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    }
    .glass {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(20px);
    }
    .dark .glass {
      background: rgba(30, 41, 59, 0.8);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-950 dark:to-slate-900 min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-300">

  <div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-4">
        <a href="index.php" class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 dark:border dark:border-gray-700 text-gray-600 dark:text-gray-300 px-4 py-2 rounded-2xl shadow-sm hover:shadow-md font-semibold text-sm transition-all hover:-translate-y-0.5">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
          Quay lại
        </a>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
          <i class="fas fa-graduation-cap text-blue-600 dark:text-blue-500"></i>
          Smart Notes
        </h1>
      </div>
      <button onclick="toggleDarkMode()" class="p-3 rounded-2xl bg-white dark:bg-gray-850 dark:border dark:border-gray-700 shadow-md hover:scale-105 transition-transform text-gray-800 dark:text-amber-400">
        <i id="themeIcon" class="fas fa-moon text-xl"></i>
      </button>
    </div>

    <div class="mb-8 max-w-3xl mx-auto">
      <div class="flex gap-3 bg-white dark:bg-gray-800 p-3 rounded-3xl shadow-lg border border-transparent dark:border-gray-700">
        <input id="youtubeUrl" type="text" 
               class="flex-1 px-6 py-4 rounded-2xl border dark:border-gray-600 bg-transparent focus:outline-none focus:border-blue-500 dark:focus:border-blue-400 text-gray-800 dark:text-white"
               placeholder="Dán link YouTube vào đây...">
        <button onclick="loadYouTube()" 
                class="bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white px-10 rounded-2xl font-semibold flex items-center gap-2 transition-colors">
          <i class="fab fa-youtube"></i> Tải Video
        </button>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
      <div class="flex-1">
        <div id="playerContainer" class="bg-black rounded-3xl overflow-hidden shadow-2xl aspect-video relative border dark:border-gray-800">
          <div id="youtubePlayer" class="w-full h-full flex items-center justify-center text-white p-4 text-center">
            Vui lòng dán link YouTube và bấm "Tải Video"
          </div>
        </div>
        <div class="mt-4 bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-lg flex items-center justify-between border border-transparent dark:border-gray-700">
          <div id="currentTime" class="font-mono text-3xl font-bold text-blue-600 dark:text-blue-400">00:00</div>
          <button onclick="addCurrentTimestamp()" 
                  class="flex items-center gap-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-7 py-4 rounded-2xl font-semibold transition-colors">
            <i class="fas fa-plus"></i> Ghi chú lúc này
          </button>
        </div>
      </div>

      <div class="w-full lg:w-[460px] bg-white dark:bg-gray-800 rounded-3xl shadow-2xl flex flex-col h-[88vh] glass border dark:border-gray-700">
        <div class="p-6 border-b dark:border-gray-700">
          <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Ghi chú theo giây</h2>
        </div>

        <div class="p-6 space-y-4 border-b dark:border-gray-700">
          <textarea id="noteContent" rows="4" 
            class="w-full p-5 rounded-3xl border dark:border-gray-650 bg-transparent text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 resize-y"
            placeholder="Nhập nội dung ghi chú..."></textarea>

          <div class="flex gap-3">
            <button onclick="saveNote()" 
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white py-4 rounded-3xl font-semibold flex items-center justify-center gap-2 transition-colors">
              <i class="fas fa-save"></i> Lưu Note
            </button>
            <button onclick="exportNotes()" 
                    class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white py-4 rounded-3xl font-semibold transition-colors">
              📤 Export
            </button>
          </div>
        </div>

        <div class="px-6 py-4 border-b dark:border-gray-700 relative">
          <div class="absolute inset-y-0 left-10 flex items-center pointer-events-none text-gray-400">
            <i class="fas fa-search"></i>
          </div>
          <input id="searchInput" placeholder="Tìm kiếm ghi chú..." 
                 class="w-full pl-11 pr-4 py-4 rounded-3xl border dark:border-gray-650 bg-transparent text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
        </div>

        <div id="noteList" class="flex-1 overflow-y-auto p-6 space-y-4"></div>

        <div class="p-5 border-t dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400 flex justify-between">
          <span id="noteCount">0 ghi chú</span>
        </div>
      </div>
    </div>
  </div>

  <div id="editModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-3xl p-8 w-full max-w-lg mx-4">
      <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Chỉnh sửa ghi chú</h3>
      <textarea id="editContent" class="w-full h-40 p-5 rounded-3xl border dark:border-gray-600 bg-transparent text-gray-800 dark:text-white mb-3"></textarea>
      <div class="flex gap-3 mt-6">
        <button onclick="closeModal()" class="flex-1 py-4 border dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-3xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Hủy</button>
        <button onclick="saveEdit()" class="flex-1 py-4 bg-blue-600 dark:bg-blue-500 text-white rounded-3xl hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">Lưu thay đổi</button>
      </div>
    </div>
  </div>

  <script>
    let player;
    let notes = [];
    let editingNoteId = null;
    let currentVideoId = null;

    function formatTime(seconds) {
      const m = Math.floor(seconds / 60);
      const s = Math.floor(seconds % 60);
      return `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
    }

    // Load YouTube
    window.loadYouTube = function() {
      const url = document.getElementById('youtubeUrl').value.trim();
      if (!url) return alert("Vui lòng dán link YouTube!");

      const regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
      const match = url.match(regExp);
      const videoId = match ? match[1] : url;

      if (!videoId || videoId.length !== 11) {
        return alert("Đường dẫn YouTube không hợp lệ!");
      }

      currentVideoId = videoId;
      document.getElementById('searchInput').value = '';

      if (player && typeof player.destroy === 'function') {
        player.destroy();
      }

      document.getElementById('playerContainer').innerHTML = '<div id="youtubePlayer"></div>';

      player = new YT.Player('youtubePlayer', {
        width: '100%',
        height: '100%',
        videoId: videoId,
        playerVars: { autoplay: 1, controls: 1, rel: 0 },
        events: { 'onReady': onPlayerReady }
      });
    };

    function onPlayerReady() {
      loadNotes();
    }

    setInterval(() => {
      if (player && player.getCurrentTime && typeof player.getCurrentTime === 'function') {
        try {
          document.getElementById('currentTime').textContent = formatTime(player.getCurrentTime());
        } catch(e) {}
      }
    }, 500);

    window.addCurrentTimestamp = () => {
      if (!player || !player.getCurrentTime) return alert("Vui lòng tải video trước!");
      const timeStr = `[${formatTime(player.getCurrentTime())}] `;
      document.getElementById('noteContent').value = timeStr + document.getElementById('noteContent').value;
    };

    window.saveNote = () => {
      if (!player || !player.getCurrentTime) return alert("Vui lòng tải video YouTube trước!");

      const content = document.getElementById('noteContent').value.trim();

      if (!content) return alert("Vui lòng nhập nội dung ghi chú!");

      const newNote = {
        note_id: Date.now(),
        timestamp: Math.floor(player.getCurrentTime()),
        note_content: content
      };

      notes.unshift(newNote);
      localStorage.setItem(`notes_${currentVideoId}`, JSON.stringify(notes));

      document.getElementById('noteContent').value = '';
      document.getElementById('searchInput').value = '';
      renderNotes(notes);
    };

    function loadNotes() {
      if (!currentVideoId) return;
      const saved = localStorage.getItem(`notes_${currentVideoId}`);
      notes = saved ? JSON.parse(saved) : [];
      renderNotes(notes);
    }

    function renderNotes(data) {
      const html = data.map(note => `
        <div class="note-item bg-white dark:bg-gray-700/50 p-5 rounded-3xl cursor-pointer border dark:border-gray-600" onclick="jumpToTime(${note.timestamp})">
          <div class="flex justify-between mb-3">
            <span class="bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 px-4 py-1 rounded-2xl font-mono text-sm">${formatTime(note.timestamp)}</span>
          </div>
          <p class="text-gray-700 dark:text-gray-200 whitespace-pre-wrap">${note.note_content}</p>
          <div class="mt-4 flex gap-4 text-sm">
            <button onclick="editNote(${note.note_id}); event.stopImmediatePropagation()" class="text-blue-600 dark:text-blue-400 hover:underline">Sửa</button>
            <button onclick="deleteNote(${note.note_id}); event.stopImmediatePropagation()" class="text-red-600 dark:text-red-400 hover:underline">Xóa</button>
          </div>
        </div>
       `).join('');

      const isSearching = document.getElementById('searchInput').value.trim() !== '';
      const emptyMessage = isSearching ? 'Không tìm thấy ghi chú phù hợp' : 'Chưa có ghi chú nào cho video này';

      document.getElementById('noteList').innerHTML = html || `<p class="text-center py-12 text-gray-400 dark:text-gray-500">${emptyMessage}</p>`;
      document.getElementById('noteCount').textContent = data.length + " ghi chú";
    }

    window.jumpToTime = (sec) => { 
      if (player && typeof player.seekTo === 'function') player.seekTo(sec, true); 
    };

    window.deleteNote = (id) => {
      if (confirm("Xóa ghi chú này?")) {
        notes = notes.filter(n => n.note_id !== id);
        localStorage.setItem(`notes_${currentVideoId}`, JSON.stringify(notes));
        thucHienTimKiem();
      }
    };

    window.editNote = (id) => {
      editingNoteId = id;
      const note = notes.find(n => n.note_id === id);
      document.getElementById('editContent').value = note.note_content;
      document.getElementById('editModal').classList.remove('hidden');
    };

    window.saveEdit = () => {
      const content = document.getElementById('editContent').value.trim();
      const note = notes.find(n => n.note_id === editingNoteId);
      if (note && content) {
        note.note_content = content;
        localStorage.setItem(`notes_${currentVideoId}`, JSON.stringify(notes));
        closeModal();
        thucHienTimKiem();
      }
    };

    window.closeModal = () => document.getElementById('editModal').classList.add('hidden');

    window.exportNotes = () => {
      if(notes.length === 0) return alert("Không có ghi chú nào để xuất!");
      let md = `# Ghi chú bài giảng (Video ID: ${currentVideoId})\n\n`;
      const sortedNotes = [...notes].sort((a,b) => a.timestamp - b.timestamp);
      sortedNotes.forEach(n => {
        md += `### ${formatTime(n.timestamp)}\n${n.note_content}\n\n`;
      });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(new Blob([md], {type: 'text/markdown'}));
      a.download = `notes_${currentVideoId}.md`;
      a.click();
    };

    function thucHienTimKiem() {
      const term = document.getElementById('searchInput').value.toLowerCase().trim();
      const filtered = notes.filter(n => n.note_content.toLowerCase().includes(term));
      renderNotes(filtered);
    }

    document.getElementById('searchInput').addEventListener('input', thucHienTimKiem);

    document.addEventListener('keydown', e => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const content = document.getElementById('noteContent').value.trim();
        if (content) {
          saveNote();
        } else {
          alert("Vui lòng nhập nội dung ghi chú trước!");
        }
      }
    });

    // Hàm xử lý bật/tắt Chế độ sáng tối và đổi icon tương ứng
    window.toggleDarkMode = () => {
      const isDark = document.documentElement.classList.toggle('dark');
      const icon = document.getElementById('themeIcon');
      if (isDark) {
        icon.className = 'fas fa-sun text-xl';
        localStorage.setItem('theme', 'dark');
      } else {
        icon.className = 'fas fa-moon text-xl';
        localStorage.setItem('theme', 'light');
      }
    };

    // Tự động kiểm tra và áp dụng chế độ tối của hệ thống hoặc phiên làm việc trước đó
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
      document.getElementById('themeIcon').className = 'fas fa-sun text-xl';
    } else {
      document.documentElement.classList.remove('dark');
      document.getElementById('themeIcon').className = 'fas fa-moon text-xl';
    }

    const script = document.createElement('script');
    script.src = "https://www.youtube.com/iframe_api";
    document.head.appendChild(script);
  </script>
</body>
</html>