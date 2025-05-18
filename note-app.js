// Note app main JavaScript file
const noteContainer = document.getElementById('noteContainer');
const gridBtn = document.getElementById('gridBtn');
const listBtn = document.getElementById('listBtn');
const addNoteBtn = document.getElementById('addNoteBtn');
const toggleDarkModeBtn = document.getElementById('toggleDarkMode');
const searchInput = document.getElementById('searchInput');

const userEmail = document.querySelector('meta[name="user-email"]').content;
const notesKey = 'notes_' + userEmail;
const labelsKey = 'labels_' + userEmail;

let notes = JSON.parse(localStorage.getItem(notesKey)) || [];
let labels = JSON.parse(localStorage.getItem(labelsKey)) || [];
let currentTag = '';
let searchQuery = '';

// Import lock handler
import { createLockOverlay } from './lock-handler.js';

function createNoteElement(note, index, isSharedNote) {
  const isOwner = note.owner === userEmail;
  const sharedItem = (note.shared || []).find(s => s.email === userEmail);
  const canEdit = isOwner || (sharedItem && sharedItem.permission === 'edit');
  
  const div = document.createElement('div');
  div.className = 'note-item fade-in';
  div.style.transitionDelay = (index * 30) + 'ms';

  const card = document.createElement('div');
  card.className = 'note-card keep-style';
  card.style.position = 'relative';
  card.style.background = note.color || '#fff';
  card.style.fontSize = note.fontSize || '16px';

  // If locked and not unlocked, show lock overlay and return
  if (note.locked && !note._unlocked) {
    const lockOverlay = createLockOverlay(note, index, isSharedNote);
    card.appendChild(lockOverlay);
    div.appendChild(card);
    return div;
  }

  // ... rest of createNoteElement function ...
}

// ... rest of the JavaScript code ... 