let confirmCallback = null;

function showNotification(message, type = 'info', confirm = false, onConfirm = null) {
    if (!message) return;

    let icon = '';
    let iconColorClass = 'text-blue-500'; // Default to info
    let textColor = 'text-gray-700';

    switch (type) {
        case 'success':
            icon = 'fa-check-circle';
            iconColorClass = 'text-green-500';
            break;
        case 'warning':
            icon = 'fa-exclamation-triangle';
            iconColorClass = 'text-yellow-500';
            break;
        case 'error':
            icon = 'fa-times-circle';
            iconColorClass = 'text-red-500';
            break;
        case 'info':
        default:
            icon = 'fa-info-circle';
            iconColorClass = 'text-blue-500';
            break;
    }

    const notificationDiv = document.createElement('div');
    notificationDiv.id = 'notification';
    notificationDiv.className = 'fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50';
    notificationDiv.innerHTML = `
        <div class="rounded-lg shadow-2xl p-6 max-w-sm w-full bg-white ${textColor} border-2 border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold flex items-center"><i class="fas ${icon} mr-2 ${iconColorClass}"></i> Notification</h2>
                <button onclick="closeNotification()" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-gray-700 mb-4">
                ${message}
            </div>
            <div class="flex justify-end gap-2">
                ${confirm ? `
                    <button onclick="confirmNotification()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <i class="fas fa-trash-alt mr-2"></i> Delete
                    </button>
                    <button onclick="closeNotification()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                ` : ''}
                ${!confirm ? `<button onclick="closeNotification()" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-check mr-2"></i> Okay
                </button>` : ''}
            </div>
        </div>
    `;

    document.body.appendChild(notificationDiv);
}

function closeNotification() {
    const notificationDiv = document.getElementById("notification");
    if (notificationDiv) {
        notificationDiv.remove();
    }
}

function confirmNotification() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeNotification();
}

function showConfirmation(message, onConfirm) {
    confirmCallback = onConfirm;
    showNotification(message, 'warning', true);
}
