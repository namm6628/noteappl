// Lock handler functions
function createLockOverlay(note, index, isSharedNote) {
  const lockOverlay = document.createElement('div');
  lockOverlay.className = 'lock-overlay';
  lockOverlay.innerHTML = `
    <div class="lock-content">
      <div class="lock-icon">
        <i class="fas fa-lock"></i>
      </div>
      <div class="lock-title">Ghi chú được bảo vệ</div>
      <div class="lock-input-group">
        <input type="password" class="lock-input" placeholder="Nhập mật khẩu">
        <button class="unlock-btn">
          <i class="fas fa-unlock"></i>
          <span>Mở khóa</span>
        </button>
      </div>
      <div class="lock-error"></div>
    </div>
  `;

  const pwdInput = lockOverlay.querySelector('.lock-input');
  const unlockBtn = lockOverlay.querySelector('.unlock-btn');
  const errorDiv = lockOverlay.querySelector('.lock-error');

  const showError = (message) => {
    errorDiv.textContent = message;
    errorDiv.classList.add('show');
    pwdInput.classList.add('error');
    setTimeout(() => {
      errorDiv.classList.remove('show');
      pwdInput.classList.remove('error');
    }, 3000);
  };

  const unlockNote = () => {
    if (btoa(pwdInput.value) === note.password) {
      lockOverlay.classList.add('unlock-success');
      setTimeout(() => {
        note._unlocked = true;
        if (!isSharedNote) {
          notes[index]._unlocked = true;
          saveNotes();
        } else {
          let myNotes = JSON.parse(localStorage.getItem(notesKey)) || [];
          const myIdx = myNotes.findIndex(n => n.id === note.id);
          if (myIdx !== -1) {
            myNotes[myIdx]._unlocked = true;
            localStorage.setItem(notesKey, JSON.stringify(myNotes));
          }
        }
        renderNotes();
      }, 500);
    } else {
      showError('Mật khẩu không đúng!');
      pwdInput.value = '';
      pwdInput.focus();
    }
  };

  unlockBtn.onclick = unlockNote;
  pwdInput.onkeydown = (e) => {
    if (e.key === 'Enter') unlockNote();
  };

  return lockOverlay;
} 