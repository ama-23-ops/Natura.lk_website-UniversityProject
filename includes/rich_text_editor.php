<?php
function initRichTextEditor($elementId, $height = '300px') {
    ob_start();
?>
<style>
.rich-text-editor-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.75);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease-in-out;
}

.rich-text-editor-overlay.active {
    opacity: 1;
    visibility: visible;
}

.rich-text-editor-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.95);
    width: 95%;
    max-width: 900px;
    max-height: 90vh;
    background: white;
    border-radius: 1rem;
    z-index: 1001;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease-in-out;
}

.rich-text-editor-modal.active {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
    visibility: visible;
}

.rich-text-editor {
    display: flex;
    flex-direction: column;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    overflow: hidden;
    height: 100%;
    max-height: 90vh;
}

.editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.editor-close-btn {
    position: absolute;
    top: -15px;
    right: -15px;
    width: 35px;
    height: 35px;
    background: #ef4444;
    border: 2px solid #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.editor-close-btn:hover {
    transform: rotate(90deg);
    background: #dc2626;
}

.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.75rem;
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    overflow-x: auto;
}

.editor-toolbar button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border: none;
    background: transparent;
    color: #475569;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
}

.editor-toolbar button:hover {
    background-color: #e2e8f0;
    color: #1e293b;
}

.editor-toolbar button.active {
    background-color: #0d9488;
    color: white;
}

.editor-toolbar .separator {
    width: 1px;
    height: 24px;
    background-color: #e2e8f0;
    margin: 0 0.5rem;
}

.editor-content {
    flex: 1;
    padding: 1rem;
    background-color: white;
    min-height: <?= $height ?>;
    max-height: calc(90vh - 200px);
    overflow-y: auto;
    outline: none;
}

.editor-content:focus {
    outline: 2px solid #0d9488;
    outline-offset: -2px;
}

.editor-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.editor-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
}

.editor-btn-primary {
    background-color: #0d9488;
    color: white;
}

.editor-btn-primary:hover {
    background-color: #0f766e;
}

.editor-btn-secondary {
    background-color: #e2e8f0;
    color: #475569;
}

.editor-btn-secondary:hover {
    background-color: #cbd5e1;
}

@media (max-width: 640px) {
    .editor-toolbar {
        padding: 0.5rem;
        gap: 0.25rem;
    }
    
    .editor-toolbar button {
        padding: 0.375rem;
    }
    
    .separator {
        margin: 0 0.25rem;
    }
}
</style>

<div class="rich-text-editor-overlay" id="<?= $elementId ?>-overlay">
    <div class="rich-text-editor-modal" id="<?= $elementId ?>-modal">
        <button class="editor-close-btn" onclick="closeRichTextEditor('<?= $elementId ?>')">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="rich-text-editor">
            <div class="editor-header">
                <h3 class="text-lg font-semibold text-gray-800">Rich Text Editor</h3>
            </div>
            
            <div class="editor-toolbar" id="<?= $elementId ?>-toolbar">
                <!-- Text Style -->
                <button type="button" data-command="bold" title="Bold">
                    <i class="fas fa-bold"></i>
                </button>
                <button type="button" data-command="italic" title="Italic">
                    <i class="fas fa-italic"></i>
                </button>
                <button type="button" data-command="underline" title="Underline">
                    <i class="fas fa-underline"></i>
                </button>
                <button type="button" data-command="strikeThrough" title="Strike Through">
                    <i class="fas fa-strikethrough"></i>
                </button>
                
                <div class="separator"></div>
                
                <!-- Lists with Improved Handling -->
                <button type="button" data-command="insertUnorderedList" title="Bullet List">
                    <i class="fas fa-list-ul"></i>
                </button>
                <button type="button" data-command="insertOrderedList" title="Numbered List">
                    <i class="fas fa-list-ol"></i>
                </button>
                <button type="button" data-command="indent" title="Increase Indent">
                    <i class="fas fa-indent"></i>
                </button>
                <button type="button" data-command="outdent" title="Decrease Indent">
                    <i class="fas fa-outdent"></i>
                </button>
                
                <div class="separator"></div>
                
                <!-- Alignment -->
                <button type="button" data-command="justifyLeft" title="Align Left">
                    <i class="fas fa-align-left"></i>
                </button>
                <button type="button" data-command="justifyCenter" title="Align Center">
                    <i class="fas fa-align-center"></i>
                </button>
                <button type="button" data-command="justifyRight" title="Align Right">
                    <i class="fas fa-align-right"></i>
                </button>
                <button type="button" data-command="justifyFull" title="Justify">
                    <i class="fas fa-align-justify"></i>
                </button>
                
                <div class="separator"></div>
                
                <!-- Additional Formatting -->
                <button type="button" data-command="insertParagraph" title="Insert Paragraph">
                    <i class="fas fa-paragraph"></i>
                </button>
                <button type="button" data-command="createLink" title="Insert Link">
                    <i class="fas fa-link"></i>
                </button>
                <button type="button" data-command="unlink" title="Remove Link">
                    <i class="fas fa-unlink"></i>
                </button>
                
                <div class="separator"></div>
                
                <!-- Undo/Redo -->
                <button type="button" data-command="undo" title="Undo">
                    <i class="fas fa-undo"></i>
                </button>
                <button type="button" data-command="redo" title="Redo">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
            
            <div class="editor-content" id="<?= $elementId ?>" contenteditable="true"></div>
            
            <div class="editor-footer">
                <button type="button" class="editor-btn editor-btn-secondary" onclick="clearEditor('<?= $elementId ?>')">
                    <i class="fas fa-trash-alt"></i>
                    Clear
                </button>
                <button type="button" class="editor-btn editor-btn-primary" onclick="saveAndCloseEditor('<?= $elementId ?>')">
                    <i class="fas fa-save"></i>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-200"
     onclick="openRichTextEditor('<?= $elementId ?>')"
     id="<?= $elementId ?>-preview">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-edit text-teal-600"></i>
            <span class="text-gray-600">Click to edit content</span>
        </div>
        <i class="fas fa-expand-alt text-gray-400"></i>
    </div>
    <div class="mt-2 text-gray-500 text-sm preview-content"></div>
</div>

<textarea name="<?= $elementId ?>_content" id="<?= $elementId ?>_content" style="display: none;"></textarea>

<script>
function openRichTextEditor(elementId) {
    const overlay = document.getElementById(`${elementId}-overlay`);
    const modal = document.getElementById(`${elementId}-modal`);
    
    overlay.classList.add('active');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeRichTextEditor(elementId) {
    const overlay = document.getElementById(`${elementId}-overlay`);
    const modal = document.getElementById(`${elementId}-modal`);
    
    overlay.classList.remove('active');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

function clearEditor(elementId) {
    if (confirm('Are you sure you want to clear all content?')) {
        const editor = document.getElementById(elementId);
        editor.innerHTML = '';
        updatePreview(elementId);
    }
}

function saveAndCloseEditor(elementId) {
    const editor = document.getElementById(elementId);
    const hiddenInput = document.getElementById(`${elementId}_content`);
    hiddenInput.value = editor.innerText;
    updatePreview(elementId);
    closeRichTextEditor(elementId);
}

function updatePreview(elementId) {
    const editor = document.getElementById(elementId);
    const preview = document.getElementById(`${elementId}-preview`).querySelector('.preview-content');
    const content = editor.innerText.trim();
    preview.textContent = content.length > 100 ? content.substring(0, 100) + '...' : content;
}

(function() {
    const elementId = '<?= $elementId ?>';
    const editor = document.getElementById(elementId);
    const toolbar = document.getElementById(`${elementId}-toolbar`);
    const hiddenInput = document.getElementById(`${elementId}_content`);
    const overlay = document.getElementById(`${elementId}-overlay`);
    
    // Initialize with any existing content
    if (hiddenInput.value) {
        editor.innerHTML = hiddenInput.value;
        updatePreview(elementId);
    }
    
    // Enhanced list handling
    editor.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && document.queryCommandState('insertOrderedList')) {
            const selection = window.getSelection();
            const range = selection.getRangeAt(0);
            const li = range.startContainer.parentElement;
            
            if (li.tagName === 'LI' && li.textContent.trim() === '') {
                e.preventDefault();
                const list = li.parentElement;
                const newP = document.createElement('p');
                newP.innerHTML = '<br>';
                list.parentNode.insertBefore(newP, list.nextSibling);
                li.parentNode.removeChild(li);
                
                const newRange = document.createRange();
                newRange.setStart(newP, 0);
                newRange.collapse(true);
                selection.removeAllRanges();
                selection.addRange(newRange);
            }
        }
    });
    
    // Handle toolbar buttons with improved state tracking
    toolbar.addEventListener('click', (e) => {
        const button = e.target.closest('button');
        if (!button) return;
        
        e.preventDefault();
        const command = button.dataset.command;
        
        if (command === 'createLink') {
            const url = prompt('Enter URL:', 'http://');
            if (url) document.execCommand(command, false, url);
        } else {
            document.execCommand(command, false, null);
        }
        
        // Update active states for all applicable buttons
        updateToolbarStates();
        
        editor.focus();
    });
    
    // Function to update toolbar button states
    function updateToolbarStates() {
        toolbar.querySelectorAll('button').forEach(button => {
            const command = button.dataset.command;
            if (['bold', 'italic', 'underline', 'strikeThrough', 
                 'insertOrderedList', 'insertUnorderedList',
                 'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'].includes(command)) {
                button.classList.toggle('active', document.queryCommandState(command));
            }
        });
    }
    
    // Track selection changes to update toolbar states
    editor.addEventListener('keyup', updateToolbarStates);
    editor.addEventListener('mouseup', updateToolbarStates);
    editor.addEventListener('input', () => {
        updateToolbarStates();
        updatePreview(elementId);
    });
    
    // Enhanced paste handling
    editor.addEventListener('paste', (e) => {
        e.preventDefault();
        
        // Get pasted content
        let content = (e.originalEvent || e).clipboardData.getData('text/html') ||
                     (e.originalEvent || e).clipboardData.getData('text/plain');
        
        // If it's HTML, clean it
        if (content.includes('<')) {
            const div = document.createElement('div');
            div.innerHTML = content;
            content = div.innerText;
        }
        
        // Insert at cursor position
        document.execCommand('insertText', false, content);
        updatePreview(elementId);
    });
    
    // Initialize toolbar states
    updateToolbarStates();
})();
</script>
<?php
    return ob_get_clean();
}

// Helper function to get editor content
function getRichTextContent($elementId) {
    return isset($_POST["{$elementId}_content"]) ? $_POST["{$elementId}_content"] : '';
}
?>