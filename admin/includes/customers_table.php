<div class="min-h-screen ">
  <style>
    .teal-gradient {
      background: linear-gradient(135deg, #00796b 0%, #009688 100%);
    }
  </style>
  <div class="teal-gradient rounded-lg p-6 mb-8 text-white shadow-lg">
    <div class="flex items-center">
      <i class="fas fa-users text-4xl mr-4"></i>
      <div>
        <h1 class="text-3xl font-bold">MANAGE CUSTOMERS</h1>
        <p class="text-teal-100">VIEW AND MANAGE USER ACCOUNTS, ROLES, AND PERMISSIONS</p>
      </div>
    </div>
  </div>

  <!-- Search and Filter Section -->
  <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
               placeholder="Search users..." 
               class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-teal-600 focus:border-transparent">
      </div>
      <div class="w-40">
        <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-teal-600 focus:border-transparent">
          <option value="">All Status</option>
          <option value="active" <?= ($status_filter === 'active') ? 'selected' : '' ?>>Active</option>
          <option value="disabled" <?= ($status_filter === 'disabled') ? 'selected' : '' ?>>Disabled</option>
        </select>
      </div>
      <div class="w-40">
        <select name="role" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-teal-600 focus:border-transparent">
          <option value="">All Roles</option>
          <option value="1" <?= ($role_filter === '1') ? 'selected' : '' ?>>Admin</option>
          <option value="0" <?= ($role_filter === '0') ? 'selected' : '' ?>>User</option>
        </select>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
          <i class="fas fa-search mr-2"></i>FILTER
        </button>
        <a href="customers.php" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
          <i class="fas fa-redo mr-2"></i>RESET
        </a>
      </div>
    </form>
  </div>

  <!-- Results count -->
  <div class="mb-4 text-gray-600">
    Showing <?= min($offset + 1, $totalRecords) ?> to <?= min($offset + $limit, $totalRecords) ?> of <?= $totalRecords ?> customers
  </div>

  <!-- Users Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php foreach ($users as $user) : ?>
      <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col">
        <div class="flex-1 space-y-4">
          <!-- Profile Picture -->
          <div class="flex justify-center mb-4">
            <?php if (!empty($user['profile_picture'])): ?>
              <img src="<?= $user['profile_picture'] ?>" alt="Profile Picture" class="rounded-full w-20 h-20 object-cover border-2 border-blue-100">
            <?php else: ?>
              <div class="rounded-full w-20 h-20 bg-gray-200 flex items-center justify-center">
                <i class="fas fa-user-circle text-4xl text-gray-400"></i>
              </div>
            <?php endif; ?>
          </div>
          
          <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-user-circle text-blue-500"></i>
            <?= $user['name'] ?>
          </h2>
          
          <div class="space-y-2">
            <p class="text-gray-600 flex items-center gap-2">
              <i class="fas fa-at text-gray-400"></i>
              <?= $user['username'] ?>
            </p>
            <p class="text-gray-600 flex items-center gap-2">
              <i class="fas fa-envelope text-gray-400"></i>
              <?= $user['email'] ?>
            </p>
            <p class="text-gray-600 flex items-center gap-2">
              <i class="fas fa-phone text-gray-400"></i>
              <?= $user['contact_number'] ?>
            </p>
            <p class="<?= $user['status'] == 'active' ? 'text-green-600' : 'text-red-600' ?> flex items-center gap-2">
              <i class="fas <?= $user['status'] == 'active' ? 'fa-check-circle' : 'fa-ban' ?> text-gray-400"></i>
              Status: <?= ucfirst($user['status']) ?>
            </p>
          </div>

          <form method="post" class="mt-4">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <select name="is_admin" onchange="this.form.submit()" 
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="0" <?= !$user['is_admin'] ? 'selected' : '' ?>>User</option>
              <option value="1" <?= $user['is_admin'] ? 'selected' : '' ?>>Admin</option>
            </select>
            <input type="hidden" name="update_role">
          </form>

          <div class="flex items-center justify-end space-x-4 mt-4 pt-4 border-t border-gray-100 pb-2">
            <a href="customers_edit.php?id=<?= $user['id'] ?>" 
               class="p-2 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
              <i class="fas fa-edit text-lg"></i>
            </a>
            <a href="customers.php?action=delete&id=<?= $user['id'] ?>" 
               class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
               onclick="return confirm('Are you sure you want to delete this user?')">
              <i class="fas fa-trash-alt text-lg"></i>
            </a>
            <?php if ($user['status'] == 'active') : ?>
              <a href="customers.php?action=disable&id=<?= $user['id'] ?>" 
                 class="p-2 text-yellow-500 hover:text-yellow-700 hover:bg-yellow-50 rounded-lg transition-colors"
                 onclick="return confirm('Are you sure you want to disable this user?')">
                <i class="fas fa-user-slash text-lg"></i>
              </a>
            <?php else : ?>
              <a href="customers.php?action=enable&id=<?= $user['id'] ?>" 
                 class="p-2 text-green-500 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                 onclick="return confirm('Are you sure you want to enable this user?')">
                <i class="fas fa-user-check text-lg"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="mt-6 flex justify-center">
    <div class="flex space-x-2">
      <?php if ($page > 1): ?>
        <a href="?page=<?= ($page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>" 
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
          Previous
        </a>
      <?php endif; ?>

      <?php
      $start = max(1, $page - 2);
      $end = min($totalPages, $page + 2);
      
      if ($start > 1) {
        echo '<span class="px-4 py-2 text-gray-600">...</span>';
      }
      
      for ($i = $start; $i <= $end; $i++):
      ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>" 
           class="px-4 py-2 <?= ($i === $page) ? 'bg-teal-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50 text-gray-700' ?> rounded-lg">
          <?= $i ?>
        </a>
      <?php endfor; 
      
      if ($end < $totalPages) {
        echo '<span class="px-4 py-2 text-gray-600">...</span>';
      }
      ?>

      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= ($page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>" 
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
          Next
        </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
