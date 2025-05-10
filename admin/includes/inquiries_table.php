<div class="min-h-screen bg-gray-50 ">
    <style>
        .message-content {
            max-height: 100px;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .message-content.expanded {
            max-height: none;
        }
    </style>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <div class="bg-teal-100 rounded-full p-3">
                    <i class="fas fa-inbox text-2xl text-teal-600"></i>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-800">CUSTOMER INQUIRIES</h1>
                    <p class="text-gray-500">MANAGE AND RESPOND TO CUSTOMER MESSAGES</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Inquiries -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100">TOTAL INQUIRIES</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['total_inquiries']; ?></h3>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-comments text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- New Inquiries -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100">NEW</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['new_inquiries']; ?></h3>
                    </div>
                    <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-bell text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Read Inquiries -->
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100">READ</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['read_inquiries']; ?></h3>
                    </div>
                    <div class="bg-yellow-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-eye text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Responded Inquiries -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100">RESPONDED</p>
                        <h3 class="text-2xl font-bold"><?php echo $stats['responded_inquiries']; ?></h3>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                           placeholder="Search inquiries..." 
                           class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="responded" <?php echo $status_filter === 'responded' ? 'selected' : ''; ?>>Responded</option>
                </select>
            </div>
            <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded-lg hover:bg-teal-700 transition-colors flex items-center justify-center">
                <i class="fas fa-filter mr-2"></i>APPLY FILTERS
            </button>
        </form>
    </div>

    <!-- Inquiries Grid -->
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($inquiries as $inquiry): ?>
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <?php
                    $statusColor = match($inquiry['status']) {
                        'new' => 'bg-red-500',
                        'read' => 'bg-yellow-500',
                        'responded' => 'bg-green-500',
                        default => 'bg-gray-500'
                    };
                ?>
                <!-- Card Header Section -->
                <div class="border-b border-gray-100">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="bg-teal-100 rounded-full p-2.5">
                                <i class="fas fa-user text-teal-600"></i>
                            </div>
                            <div class="<?php echo $statusColor; ?> text-white px-3 py-1 rounded-full text-xs font-medium">
                                <?php echo ucfirst($inquiry['status']); ?>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <h2 class="text-lg font-semibold text-gray-800 truncate">
                                <?php echo htmlspecialchars($inquiry['user_name'] ? $inquiry['user_name'] : $inquiry['name']); ?>
                            </h2>
                            <p class="text-sm text-gray-600 flex items-center">
                                <i class="fas fa-envelope mr-2"></i>
                                <span class="truncate"><?php echo htmlspecialchars($inquiry['email']); ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Content Section -->
                <div class="p-4 space-y-4">

                    <!-- Message Section -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Message:</h3>
                        <div class="message-content bg-gray-50 p-3 rounded-lg text-sm" id="message-<?php echo $inquiry['id']; ?>">
                            <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                        </div>
                        <button onclick="toggleMessage('message-<?php echo $inquiry['id']; ?>')" 
                                class="text-teal-600 hover:text-teal-800 text-xs mt-2 flex items-center">
                            <i class="fas fa-chevron-down mr-1"></i>Show More
                        </button>
                    </div>

                    <!-- Timestamp -->
                    <div class="text-xs text-gray-500 flex items-center">
                        <i class="far fa-clock mr-2"></i>
                        <?php echo date('F j, Y g:i A', strtotime($inquiry['inquiry_date'])); ?>
                    </div>

                    <!-- Admin Response Section -->
                    <?php if ($inquiry['status'] === 'responded'): ?>
                        <div class="bg-green-50 p-3 rounded-lg space-y-2">
                            <h3 class="text-sm font-medium text-green-700">Admin Response:</h3>
                            <p class="text-sm text-green-600"><?php echo nl2br(htmlspecialchars($inquiry['admin_response'])); ?></p>
                            <p class="text-xs text-green-500 flex items-center">
                                <i class="far fa-clock mr-2"></i>
                                <?php echo date('F j, Y g:i A', strtotime($inquiry['response_date'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <?php if ($inquiry['status'] === 'new'): ?>
                            <a href="?mark_read=<?php echo $inquiry['id']; ?>" 
                               class="bg-yellow-500 text-white px-3 py-1.5 rounded-lg hover:bg-yellow-600 transition-colors text-sm">
                                <i class="fas fa-check mr-1.5"></i>Mark as Read
                            </a>
                        <?php endif; ?>

                        <?php if ($inquiry['status'] !== 'responded'): ?>
                            <button onclick="showResponseForm('<?php echo $inquiry['id']; ?>')"
                                    class="bg-teal-600 text-white px-3 py-1.5 rounded-lg hover:bg-teal-700 transition-colors text-sm">
                                <i class="fas fa-reply mr-1.5"></i>Respond
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Response Form (Hidden by default) -->
                    <div id="response-form-<?php echo $inquiry['id']; ?>" class="hidden pt-3">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="space-y-3">
                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                            <textarea name="response" rows="3" 
                                    class="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                    placeholder="Type your response here..." required></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" 
                                        onclick="hideResponseForm('<?php echo $inquiry['id']; ?>')"
                                        class="bg-gray-500 text-white px-3 py-1.5 rounded-lg hover:bg-gray-600 transition-colors text-sm">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="bg-teal-600 text-white px-3 py-1.5 rounded-lg hover:bg-teal-700 transition-colors text-sm">
                                    <i class="fas fa-paper-plane mr-1.5"></i>Send Response
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="bg-white rounded-lg shadow-md p-4 mt-6">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalRecords); ?> of <?php echo $totalRecords; ?> inquiries
            </div>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php
                $start = max(1, $page - 2);
                $end = min($start + 4, $totalPages);
                if ($end - $start < 4) $start = max(1, $end - 4);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" 
                       class="px-4 py-2 <?php echo $i === $page ? 'bg-teal-600 text-white' : 'bg-white text-gray-500 border border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition-colors">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleMessage(id) {
    const element = document.getElementById(id);
    element.classList.toggle('expanded');
    const button = element.nextElementSibling;
    const isExpanded = element.classList.contains('expanded');
    button.innerHTML = `<i class="fas fa-chevron-${isExpanded ? 'up' : 'down'} mr-1"></i>${isExpanded ? 'Show Less' : 'Show More'}`;
}

function showResponseForm(id) {
    document.getElementById(`response-form-${id}`).classList.remove('hidden');
}

function hideResponseForm(id) {
    document.getElementById(`response-form-${id}`).classList.add('hidden');
}
</script>
