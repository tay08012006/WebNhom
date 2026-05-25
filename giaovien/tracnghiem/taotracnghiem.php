<?php
ini_set('session.name', 'GV_SESSION');
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Chỉ giáo viên mới có quyền truy cập.");
}
$ma_lop = $_GET['malop'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Đề Trắc Nghiệm - Kéo Thả</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', system-ui, sans-serif; }
        .question-card { transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
        .question-card:hover { transform: translateY(-6px); box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.4); }
        .drop-zone.dragover { background: #1e3a8a; border-color: #60a5fa; transform: scale(1.01); }
        .type-item { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .type-item:hover { transform: translateY(-4px) scale(1.03); }
        .back-btn:hover { transform: translateX(-5px); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen">

<div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="../phonghoc.php?malop=<?= urlencode($ma_lop) ?>&tab=bai-tap" 
               class="back-btn inline-flex items-center gap-3 px-6 py-3.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl font-medium">
                <i class="fas fa-arrow-left"></i>
                <span>Quay về lớp học</span>
            </a>
            <h1 class="text-3xl font-bold">Tạo Đề Trắc Nghiệm</h1>
        </div>
        
        <div class="flex items-center gap-4">
            <button onclick="toggleTheme()" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-all">
                <i id="theme-icon" class="fas fa-moon text-xl"></i>
            </button>
            <button onclick="saveQuiz()" class="bg-emerald-600 hover:bg-emerald-500 px-8 py-3.5 rounded-2xl font-semibold flex items-center gap-3 transition-all active:scale-95">
                <i class="fas fa-save"></i>
                LƯU & ĐĂNG BÀI
            </button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- LEFT SIDEBAR -->
        <div class="col-span-12 lg:col-span-3">
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 sticky top-6 border border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-3">
                    <i class="fas fa-layer-group text-blue-500"></i>
                    CÁC DẠNG CÂU HỎI
                </h3>
                
                <div class="space-y-3">
                    <div draggable="true" ondragstart="dragStart(event)" data-type="mc" class="type-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-blue-400 rounded-2xl p-5 cursor-grab flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-2xl">📝</div>
                        <div><div class="font-semibold">Trắc nghiệm</div><div class="text-sm text-gray-500 dark:text-gray-400"></div></div>
                    </div>

                    <div draggable="true" ondragstart="dragStart(event)" data-type="tf" class="type-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-emerald-400 rounded-2xl p-5 cursor-grab flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center text-2xl">✅</div>
                        <div><div class="font-semibold">Đúng / Sai</div><div class="text-sm text-gray-500 dark:text-gray-400"></div></div>
                    </div>

                    <div draggable="true" ondragstart="dragStart(event)" data-type="fill" class="type-item bg-white dark:bg-gray-800 border border-violet-400 rounded-2xl p-5 cursor-grab flex items-center gap-4 ring-2 ring-violet-300 dark:ring-violet-500/30">
                        <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 rounded-xl flex items-center justify-center text-2xl">✍️</div>
                        <div><div class="font-semibold">Điền chỗ trống</div><div class="text-sm text-gray-500 dark:text-gray-400"></div></div>
                    </div>
                </div>

                <!-- Thêm nhanh -->
                <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold mb-4 text-emerald-600 dark:text-emerald-400">⚡ THÊM NHANH CÁC CÂU HỎI</h4>
                    <select id="bulk_type" class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-2xl px-4 py-3 mb-3">
                        <option value="mc">Trắc nghiệm</option>
                        <option value="tf">Đúng / Sai</option>
                        <option value="fill">Điền chỗ trống</option>
                    </select>
                    <div class="flex gap-3">
                        <input type="number" id="bulk_count" value="20" min="1" max="100" 
                               class="bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-2xl px-5 py-4 w-28 text-center text-xl">
                        <button onclick="addBulkQuestions()" id="bulk-btn"
                                class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-4 rounded-2xl transition-all active:scale-95">
                            Thêm Câu
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN AREA -->
        <div class="col-span-12 lg:col-span-9">
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 border border-gray-200 dark:border-gray-800">
                <div class="flex flex-wrap gap-4 mb-8">
                    <input type="text" id="quiz_title" class="flex-1 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 focus:border-blue-500 rounded-2xl px-6 py-4 text-xl font-medium outline-none" placeholder="Nhập tiêu đề đề thi..." required>
                    <div class="flex items-center bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-2xl px-6">
                        <span class="text-gray-500 dark:text-gray-400 mr-3">⏱</span>
                        <input type="number" id="duration_minutes" value="15" min="5" max="180" class="bg-transparent w-16 text-center text-xl font-semibold outline-none">
                        <span class="text-gray-500 dark:text-gray-400 ml-2">phút</span>
                    </div>
                </div>

                <div id="drop-zone" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="removeDragover(event)"
                     class="drop-zone min-h-[520px] border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-3xl p-8">
                    <div id="questions-list" class="space-y-6"></div>
                    <div id="empty-state" class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 py-20">
                        <i class="fas fa-cloud-arrow-down text-7xl mb-6 opacity-60"></i>
                        <p class="text-xl">Kéo & thả hoặc dùng nút thêm nhanh</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="quizForm" action="xuly_taotracnghiem.php" method="POST" class="hidden">
    <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
    <input type="hidden" name="quiz_title" id="form_title">
    <input type="hidden" name="duration_minutes" id="form_duration">
    <div id="form-questions"></div>
</form>

<script>
// Dark mode
tailwind.config = { darkMode: 'class' };

function toggleTheme() {
    document.documentElement.classList.toggle('dark');
    const icon = document.getElementById('theme-icon');
    if (document.documentElement.classList.contains('dark')) {
        icon.classList.remove('fa-moon'); icon.classList.add('fa-sun');
        localStorage.setItem('theme', 'dark');
    } else {
        icon.classList.remove('fa-sun'); icon.classList.add('fa-moon');
        localStorage.setItem('theme', 'light');
    }
}
if (localStorage.getItem('theme') === 'dark' || window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.classList.add('dark');
    document.getElementById('theme-icon').classList.add('fa-sun');
}

// Variables
let questionCounter = 0;
const questionsList = document.getElementById('questions-list');
const emptyState = document.getElementById('empty-state');

// Drag & Drop
function allowDrop(e) { e.preventDefault(); e.currentTarget.classList.add('dragover'); }
function removeDragover(e) { e.currentTarget.classList.remove('dragover'); }
function dragStart(e) { e.dataTransfer.setData("text/plain", e.target.getAttribute("data-type")); }

function drop(e) {
    e.preventDefault();
    const type = e.dataTransfer.getData("text/plain");
    e.currentTarget.classList.remove('dragover');
    addQuestion(type);
}

// Create Question
function createQuestionHTML(type) {
    const id = 'q-' + Date.now();
    if (type === 'mc') {
        return `<div class="question-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-6" id="${id}">
            <div class="flex justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="q-number font-bold text-2xl text-blue-500">Câu 1</span>
                    <span class="px-4 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm rounded-full">Trắc nghiệm</span>
                </div>
                <button onclick="removeThisQuestion(this)" class="text-red-500 hover:text-red-600 text-3xl">×</button>
            </div>
            <textarea class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-5 text-lg" rows="3" placeholder="Nhập nội dung câu hỏi..."></textarea>
            <div class="grid grid-cols-2 gap-4 mt-6">
                <input type="text" class="ans w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4" placeholder="Đáp án A">
                <input type="text" class="ans w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4" placeholder="Đáp án B">
                <input type="text" class="ans w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4" placeholder="Đáp án C">
                <input type="text" class="ans w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4" placeholder="Đáp án D">
            </div>
            <select class="correct_ans mt-5 w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4">
                <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>
            </select>
        </div>`;
    } else if (type === 'tf') {
        return `<div class="question-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-6" id="${id}">
            <div class="flex justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="q-number font-bold text-2xl text-emerald-500">Câu 1</span>
                    <span class="px-4 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-sm rounded-full">Đúng / Sai</span>
                </div>
                <button onclick="removeThisQuestion(this)" class="text-red-500 hover:text-red-600 text-3xl">×</button>
            </div>
            <textarea class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-5 text-lg" rows="3" placeholder="Nhập câu hỏi..."></textarea>
            <select class="correct_ans mt-5 w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4">
                <option value="A">Đúng (A)</option><option value="B">Sai (B)</option>
            </select>
        </div>`;
    } else {
        return `<div class="question-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-6" id="${id}">
            <div class="flex justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="q-number font-bold text-2xl text-violet-500">Câu 1</span>
                    <span class="px-4 py-1 bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 text-sm rounded-full">Điền chỗ trống</span>
                </div>
                <button onclick="removeThisQuestion(this)" class="text-red-500 hover:text-red-600 text-3xl">×</button>
            </div>
            <textarea class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-5 text-lg" rows="3" placeholder="Nhập câu hỏi (dùng ___ cho chỗ trống)..."></textarea>
            <input type="text" class="correct_ans mt-4 w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4" placeholder="Đáp án đúng">
        </div>`;
    }
}

function addQuestion(type) {
    questionCounter++;
    emptyState.style.display = 'none';
    questionsList.insertAdjacentHTML('beforeend', createQuestionHTML(type));
    updateQuestionNumbers();
}

function addBulkQuestions() {
    const type = document.getElementById('bulk_type').value;
    let count = parseInt(document.getElementById('bulk_count').value) || 10;
    count = Math.min(Math.max(count, 1), 100);

    const btn = document.getElementById('bulk-btn');
    btn.innerHTML = 'Đang thêm...';
    btn.disabled = true;

    emptyState.style.display = 'none';

    const fragment = document.createDocumentFragment();
    for (let i = 0; i < count; i++) {
        questionCounter++;
        const temp = document.createElement('div');
        temp.innerHTML = createQuestionHTML(type);
        fragment.appendChild(temp.firstElementChild);
    }
    questionsList.appendChild(fragment);
    updateQuestionNumbers();

    setTimeout(() => {
        btn.innerHTML = 'Thêm Câu';
        btn.disabled = false;
        questionsList.scrollIntoView({ behavior: "smooth", block: "end" });
    }, 300);
}

function removeThisQuestion(btn) {
    if (confirm("Xóa câu hỏi này?")) {
        btn.closest('.question-card').remove();
        updateQuestionNumbers();
    }
}

function updateQuestionNumbers() {
    document.querySelectorAll('.question-card').forEach((card, i) => {
        const num = card.querySelector('.q-number');
        if (num) num.textContent = `Câu ${i+1}`;
    });
}

function saveQuiz() {
    const title = document.getElementById('quiz_title').value.trim();
    if (!title) return alert("Vui lòng nhập tiêu đề!");

    const form = document.getElementById('quizForm');
    document.getElementById('form_title').value = title;
    document.getElementById('form_duration').value = document.getElementById('duration_minutes').value;

    const container = document.getElementById('form-questions');
    container.innerHTML = '';

    document.querySelectorAll('.question-card').forEach(card => {
        const textarea = card.querySelector('textarea');
        if (!textarea || !textarea.value.trim()) return;

        const answers = card.querySelectorAll('input.ans');
        const correct = card.querySelector('.correct_ans');

        container.innerHTML += `
            <input type="hidden" name="question_text[]" value="${textarea.value.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ans_a[]" value="${answers[0] ? answers[0].value.replace(/"/g, '&quot;') : ''}">
            <input type="hidden" name="ans_b[]" value="${answers[1] ? answers[1].value.replace(/"/g, '&quot;') : ''}">
            <input type="hidden" name="ans_c[]" value="${answers[2] ? answers[2].value.replace(/"/g, '&quot;') : ''}">
            <input type="hidden" name="ans_d[]" value="${answers[3] ? answers[3].value.replace(/"/g, '&quot;') : ''}">
            <input type="hidden" name="correct_ans[]" value="${correct ? correct.value : 'A'}">
        `;
    });

    if (container.children.length === 0) return alert("Chưa có câu hỏi nào!");
    form.submit();
}
</script>
</body>
</html>