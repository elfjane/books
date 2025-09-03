<?php
/* Smarty version 5.5.2, created on 2025-09-03 10:19:26
  from 'file:index.tpl.htm' */

/* @var \Smarty\Template $_smarty_tpl */
if ($_smarty_tpl->getCompiled()->isFresh($_smarty_tpl, array (
  'version' => '5.5.2',
  'unifunc' => 'content_68b8162e155878_39272981',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '74f9a3529eac36d18faed117a525d73b7ad951f2' => 
    array (
      0 => 'index.tpl.htm',
      1 => 1756894087,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
))) {
function content_68b8162e155878_39272981 (\Smarty\Template $_smarty_tpl) {
$_smarty_current_dir = '/var/www/html/views/templates';
?><!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>珍和智子技術百科大全</title>

<style>
  body, html {
    margin: 0; padding: 0; height: 100%;
    font-family: Arial, sans-serif; overflow: hidden;
  }

  /* ----- Light Theme ----- */
  body.light { background: #f0f0f0; color: #000; }
  body.light #loginPage { background: #f0f0f0; }
  body.light #loginBox { background: #fff; }
  body.light #loginBox input { background: #fff; border: 1px solid #ccc; color: #000; }
  body.light #loginBox button { background: #007bff; color: #fff; }
  body.light #loginBox button:hover { background: #0056b3; }
  body.light .top-bar { background: #007bff; color: white; }
  body.light .left-panel { border-right: 1px solid #ccc; }
  body.light .dirs-panel { border-bottom: 1px solid #ccc; }
  body.light .resizer-h, body.light .resizer-v { background: #ddd; }
  body.light .right-panel { background-color: #f9f9f9; color: #000; }
  body.light .item:hover { background-color: #eef; }
  body.light .item.active { background-color: #cce5ff; }
  body.light .back-btn { color: blue; }
  body.light .context-menu { background: #fff; border: 1px solid #ccc; color: #000; }
  body.light .context-menu div:hover { background-color: #f0f0f0; }

  /* ----- Dark Theme ----- */
  body.dark { background: #1e1e1e; color: #ddd; }
  body.dark #loginPage { background: #121212; }
  body.dark #loginBox { background: #2a2a2a; color: #eee; }
  body.dark #loginBox input { background: #333; border: 1px solid #555; color: #eee; }
  body.dark #loginBox button { background: #0d6efd; color: #fff; }
  body.dark #loginBox button:hover { background: #084298; }
  body.dark .top-bar { background: #333; color: #eee; }
  body.dark .left-panel { border-right: 1px solid #444; }
  body.dark .dirs-panel { border-bottom: 1px solid #444; }
  body.dark .resizer-h, body.dark .resizer-v { background: #444; }
  body.dark .right-panel { background-color: #222; color: #ddd; }
  body.dark .item:hover { background-color: #444; }
  body.dark .item.active { background-color: #555; font-weight: bold; }
  body.dark button { color: white; }
  body.dark .back-btn { color: #66aaff; }
  body.dark .context-menu { background: #2a2a2a; border: 1px solid #555; color: #eee; }
  body.dark .context-menu div:hover { background-color: #444; }

  /* 原本樣式（保留，不變動） */
  #loginPage {
    display: none; align-items: center; justify-content: center;
    height: 100%; background: #f0f0f0;
  }
  #loginBox {
    background: #fff; padding: 30px; border-radius: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.3); width: 280px;
  }
  #loginBox h2 { margin-top: 0; }
  #loginBox input {
    width: 100%; padding: 8px; margin: 6px 0;
    border: 1px solid #ccc; border-radius: 6px;
  }
  #loginBox button {
    width: 100%; padding: 10px; margin-top: 10px;
    background: #007bff; color: white;
    border: none; border-radius: 6px; cursor: pointer;
  }
  #loginBox button:hover { background: #0056b3; }
  #loginError { color: red; font-size: 14px; margin-top: 5px; }
  #fileBrowser { display: none; height: 100%; }
  .top-bar {
    height: 40px; display: flex; align-items: center; justify-content: space-between;
    padding: 0 10px; box-sizing: border-box;
  }
  .top-bar button { background: transparent; border: none; cursor: pointer; font-size: 14px; }
  .container { display: flex; height: calc(100% - 40px); width: 100%; }
  .left-panel {
    width: 20%; min-width: 150px; max-width: 80%;
    display: flex; flex-direction: column; box-sizing: border-box;
  }
  .left-inner { display: flex; flex-direction: column; flex: 1; height: 100%; }
  .dirs-panel { flex: 1; overflow-y: auto; padding: 10px; box-sizing: border-box; }
  .resizer-h { height: 5px; cursor: row-resize; }
  .files-panel { flex: 1; overflow-y: auto; padding: 10px; box-sizing: border-box; }
  .resizer-v { width: 5px; cursor: col-resize; }
  .right-panel { flex: 1; padding: 5px; box-sizing: border-box; overflow-y: auto; }
  .item { cursor: pointer; padding: 4px; }
  .current-path { font-weight: bold; margin-bottom: 5px; }
  .back-btn { cursor: pointer; text-decoration: underline; margin-bottom: 5px; display: block; }
  img.preview-img { max-width: 100%; max-height: 100%; display: block; }
  .context-menu { position: absolute; z-index: 9999; width: 160px; display: none; flex-direction: column; }
  .context-menu div { padding: 8px 12px; cursor: pointer; }
</style>

<?php echo '<script'; ?>
 src="jsonViewer.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"><?php echo '</script'; ?>
>
<link id="hljs-theme" rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css">
<?php echo '<script'; ?>
 src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"><?php echo '</script'; ?>
>
</head>
<body class="dark">

<!-- 登入頁面 -->
<div id="loginPage">
  <div id="loginBox">
    <h2>登入</h2>
    <input type="text" id="username" placeholder="帳號">
    <input type="password" id="password" placeholder="密碼">
    <button onclick="doLogin()">登入</button>
    <div id="loginError"></div>
  </div>
</div>

<!-- 珍和智子技術百科大全 -->
<div id="fileBrowser">
  <div class="top-bar">
    <span>珍和智子技術百科大全</span>
    <div>
      <button onclick="toggleTheme()">切換主題</button>
      <button onclick="doLogout()">登出</button>
    </div>
  </div>
  <div class="container">
    <!-- 你的 left-panel / resizer / right-panel 保持原樣 -->
    <div class="left-panel" id="leftPanel">
      <div class="left-inner">
        <div class="dirs-panel" id="dirsPanel">
          <div class="back-btn" id="backBtn">← 上一層</div>
          <div class="current-path" id="currentPath">/</div>
          <div id="dirList"></div>
        </div>
        <div class="resizer-h" id="resizerH"></div>
        <div class="files-panel" id="filesPanel">
          <div id="fileList"></div>
        </div>
      </div>
    </div>
    <div class="resizer-v" id="resizerV"></div>
    <div class="right-panel" id="rightPanel"></div>
  </div>
</div>

<div id="contextMenu" class="context-menu"></div>

<?php echo '<script'; ?>
>
let isLogin=false;
// ===== Dark/Light Theme =====
function applyTheme(theme){
  document.body.classList.remove("light","dark");
  document.body.classList.add(theme);
  localStorage.setItem("theme", theme);

  // highlight.js CSS
  const hljsLink=document.getElementById("hljs-theme");
  hljsLink.href = theme==="dark"
    ? "https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css"
    : "https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css";
}
function toggleTheme(){
  const current=document.body.classList.contains("dark")?"dark":"light";
  applyTheme(current==="dark"?"light":"dark");
}
(function(){ applyTheme(localStorage.getItem("theme")||"dark"); })();

// ===== 登入/登出 =====
function checkLogin() {
  fetch("auth/status").then(r => r.json()).then(data => {
    if (data.success) {
      isLogin = true;
      document.getElementById("loginPage").style.display = "none";
      document.getElementById("fileBrowser").style.display = "block";
      initFileBrowser();
    } else {
      isLogin = false;
      document.getElementById("loginPage").style.display = "flex";
      document.getElementById("fileBrowser").style.display = "none";
    }
  });
}
function doLogin() {
  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;
  fetch("auth/login", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
  }).then(r => r.json()).then(data => {
    if (data.success) checkLogin();
    else document.getElementById("loginError").textContent = data.error || "登入失敗";
  });
}
function doLogout() { fetch("auth/logout").then(r => r.json()).then(() => checkLogin()); }

// ===== 檔案瀏覽器 =====
let currentDir = "";

// 高亮選取
function setActive(div, path, type) {
  document.querySelectorAll(".item").forEach(el => el.classList.remove("active"));
  div.classList.add("active");
  localStorage.setItem("lastSelectedPath", path);
  localStorage.setItem("lastSelectedType", type);
}

// 檔案顯示
function renderMarkdown(content) {
  const rightPanel = document.getElementById("rightPanel");
  rightPanel.innerHTML = marked.parse(content);
  rightPanel.querySelectorAll('pre code').forEach(block => hljs.highlightElement(block));
}
function loadFileContent(file) {
  const rightPanel = document.getElementById("rightPanel");
  const ext = file.split('.').pop().toLowerCase();
  const imageTypes = ['png','jpg','jpeg','gif','webp'];
  if (imageTypes.includes(ext)) {
    rightPanel.innerHTML = `<img class="preview-img" src="file/readFileContent?file=${encodeURIComponent(file)}" />`;
  } else if (ext === 'md') {
    fetch(`file/readFileContent?file=${encodeURIComponent(file)}`).then(res=>res.json()).then(data=>{
      if (data.file && data.file.content !== undefined) renderMarkdown(data.file.content);
      else rightPanel.textContent = "錯誤: " + (data.error||"未知");
    });
  } else if (ext === 'json') {
    fetch(`file/readFileContent?file=${encodeURIComponent(file)}`).then(res=>res.json()).then(data=>{
      if (data.file && data.file.content !== undefined) renderJSONViewer(JSON.parse(data.file.content));
      else rightPanel.textContent = "錯誤: " + (data.error||"未知");
    });
  } else if (ext==='html'||ext==='htm'||ext==='php') {
    rightPanel.innerHTML = `<iframe src="file/readFileContent?file=${encodeURIComponent(file)}" style="width:100%; height:90vh; border:none;"></iframe>`;
  } else {
    fetch(`file/readFileContent?file=${encodeURIComponent(file)}`).then(res=>res.json()).then(data=>{
      if (data.file && data.file.content !== undefined) rightPanel.textContent = data.file.content;
      else rightPanel.textContent = "錯誤: " + (data.error||"未知");
    });
  }
}

// 右鍵選單
const contextMenu = document.getElementById("contextMenu");
function attachContextMenu(div, fullPath, type) {
  if (!isLogin) {
      return;
  }
  div.oncontextmenu = (e) => {
    e.preventDefault(); contextMenu.innerHTML="";
    const options=[
      {label:"開啟",action:()=>openItem(fullPath,type)},
      {label:"在新視窗開啟",action:()=>window.open(`file/readFileContent?file=${encodeURIComponent(fullPath)}`,"_blank")},
      {label:"刪除",action:()=>deleteItem(fullPath,type)}
    ];
    options.forEach(opt=>{
      const d=document.createElement("div");
      d.textContent=opt.label;
      d.onclick=()=>{opt.action();contextMenu.style.display="none";};
      contextMenu.appendChild(d);
    });
    contextMenu.style.top=`${e.pageY}px`; contextMenu.style.left=`${e.pageX}px`;
    contextMenu.style.display="flex";
  };
}
document.addEventListener("click",()=>contextMenu.style.display="none");
document.addEventListener("scroll",()=>contextMenu.style.display="none");

function openItem(path,type){
  if(type==="dir"){ currentDir=path; loadDirs(path); loadFiles(path);}
  else if(type==="file"){ loadFileContent(path);}
}
function deleteItem(path,type){
  if(!confirm(`確定刪除 ${type==="dir"?"資料夾":"檔案"}：${path}?`)) return;
  fetch(`file/deleteFile?file=${encodeURIComponent(path)}`,{method:"DELETE"})
  .then(res=>res.json()).then(data=>{
    if(data.success){ loadDirs(currentDir); loadFiles(currentDir);}
    else alert("刪除失敗: "+(data.error||"未知錯誤"));
  });
}

// 讀取資料夾/檔案
function loadDirs(dir=""){
  fetch(`file/readFolder?dir=${encodeURIComponent(dir)}`).then(res=>res.json()).then(data=>{
    const dirList=document.getElementById("dirList"); const currentPath=document.getElementById("currentPath");
    dirList.innerHTML=""; currentPath.textContent="/"+dir;
    if(data.dirs&&data.dirs.length){
      data.dirs.forEach(d=>{
        const fullPath=dir?dir+"/"+d:d;
        const div=document.createElement("div");
        div.textContent=d; div.className="item";
        div.onclick=()=>setActive(div,fullPath,"dir");
        div.ondblclick=()=>{setActive(div,fullPath,"dir");currentDir=fullPath;loadDirs(currentDir);loadFiles(currentDir);};
        attachContextMenu(div,fullPath,"dir"); dirList.appendChild(div);
      });
    }
  });
}
// --- 讀取檔案列表時加上 data-name ---
function loadFiles(dir=""){
  fetch(`file/readFileDisplay?dir=${encodeURIComponent(dir)}`).then(res=>res.json()).then(data=>{
    const fileList=document.getElementById("fileList");
    fileList.innerHTML="";
    if(data.files&&data.files.length){
      data.files.forEach(f=>{
        const div=document.createElement("div");
        div.textContent = f.displayName + ` (${f.size} bytes)`;
        div.className = "item";
        div.dataset.name = f.name; // <-- 保存原始檔名

        const fullPath = dir ? dir + "/" + f.name : f.name;

        div.onclick = ()=>{
          setActive(div, fullPath, "file");
          loadFileContent(fullPath);
        };
        attachContextMenu(div, fullPath, "file");
        fileList.appendChild(div);
      });
    }
  });
}

// 上一層
document.getElementById("backBtn").onclick=()=>{
  if(!currentDir) return;
  const parts=currentDir.split('/'); parts.pop();
  currentDir=parts.join('/'); loadDirs(currentDir); loadFiles(currentDir);
};

// 左右上下調整
const resizerV=document.getElementById("resizerV");
const leftPanel=document.getElementById("leftPanel");
let isResizingV=false; const savedWidth=localStorage.getItem("leftPanelWidth");
if(savedWidth) leftPanel.style.width=savedWidth;
resizerV.addEventListener("mousedown",()=>{isResizingV=true;document.body.style.cursor="col-resize";});
document.addEventListener("mousemove",e=>{if(isResizingV){const w=e.clientX;if(w>150&&w<window.innerWidth*0.8) leftPanel.style.width=`${w}px`; }});
document.addEventListener("mouseup",()=>{if(isResizingV) localStorage.setItem("leftPanelWidth",leftPanel.style.width); isResizingV=false;document.body.style.cursor="default";});

const resizerH=document.getElementById("resizerH");
const dirsPanel=document.getElementById("dirsPanel");
let isResizingH=false; const savedHeight=localStorage.getItem("dirsPanelHeight");
if(savedHeight) dirsPanel.style.height=savedHeight;
resizerH.addEventListener("mousedown",()=>{isResizingH=true;document.body.style.cursor="row-resize";});
document.addEventListener("mousemove",e=>{if(isResizingH){const h=e.clientY-leftPanel.offsetTop;if(h>100&&h<window.innerHeight-100){dirsPanel.style.height=`${h}px`;dirsPanel.style.flex="none";}}});
document.addEventListener("mouseup",()=>{if(isResizingH) localStorage.setItem("dirsPanelHeight",dirsPanel.style.height); isResizingH=false;document.body.style.cursor="default";});

// 初始化
function restoreSelection(){
  const savedPath=localStorage.getItem("lastSelectedPath");
  const savedType=localStorage.getItem("lastSelectedType");
  if(savedPath&&savedType) expandPath(savedPath,savedType);
}
// --- 修正 restoreSelection / expandPath ---
function expandPath(path,type){
  if(!path) return;
  const parts=path.split("/");
  let index=0;

  function recursiveExpand(){
    if(index>=parts.length) return;
    const subPath=parts.slice(0,index+1).join("/");

    loadDirs(parts.slice(0,index).join("/"));

    setTimeout(()=>{
      const dirName = parts[index];
      const el=Array.from(document.querySelectorAll("#dirList .item"))
                     .find(x => x.textContent === dirName);
      if(el) el.classList.add("active");

      if(index===parts.length-1 && type==="file"){
        currentDir=parts.slice(0,-1).join("/");
        loadFiles(currentDir);
        setTimeout(()=>{
          const fileName = parts[parts.length-1];
          const fileEl = Array.from(document.querySelectorAll("#fileList .item"))
                               .find(x => x.dataset.name === fileName);
          if(fileEl){
            setActive(fileEl, path, "file");
            loadFileContent(path);
          }
        }, 300);
      } else if(index<parts.length-1){
        index++;
        recursiveExpand();
      }
    }, 300);
  }

  recursiveExpand();
}

function initFileBrowser(){ loadDirs(); loadFiles(); restoreSelection(); }

// 啟動
checkLogin();
<?php echo '</script'; ?>
>
</body>
</html>
<?php }
}
