@extends('layouts.app')

@section('page-heading', 'Franchise Decision Management')

@section('content')

<!-- Add/Edit Case Modal (Moved out of flow) -->
<div id="caseModal" class="<?php echo $edit_case ? 'flex' : 'hidden'; ?> fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto print:static print:bg-white print:block">
    <div class="relative w-full max-w-7xl bg-white rounded-xl shadow-2xl overflow-hidden my-8 print:shadow-none print:my-0">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b bg-gradient-to-r from-yellow-500 to-yellow-600 text-white print:hidden">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i data-lucide="<?php echo $edit_case ? 'edit' : 'plus-circle'; ?>" class="w-5 h-5"></i>
                <?php echo $edit_case ? 'Edit Franchise Case' : 'New Franchise Case'; ?>
            </h2>
            <button type="button" onclick="closeCaseModal()" class="p-1 hover:bg-white/20 rounded-lg transition-colors cursor-pointer" title="Close">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 max-h-[80vh] overflow-y-auto print:max-h-none print:overflow-visible">
            <div id="decisionPrintArea">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="save_case">
                    <input type="hidden" name="case_id" value="<?php echo $edit_case['id'] ?? 0; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name of Applicant</label>
                            <input type="text" name="applicant_name"
                                   value="<?php echo htmlspecialchars($edit_case['applicant_name'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CASE NO.</label>
                            <input type="text" name="case_no"
                                   value="<?php echo htmlspecialchars($edit_case['case_no'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type of Application</label>
                            <input type="text" name="type_of_application"
                                   value="<?php echo htmlspecialchars($edit_case['type_of_application'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Denomination</label>
                            <input type="text" name="denomination"
                                   value="<?php echo htmlspecialchars($edit_case['denomination'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Filed</label>
                            <input type="date" name="date_filed"
                                   value="<?php echo htmlspecialchars($edit_case['date_filed'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date"
                                   value="<?php echo htmlspecialchars($edit_case['expiry_date'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Units (maximum 20)</h3>
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">#</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">MAKE</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">MOTOR NO.</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">CHASIS NO.</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">PLATE NO.</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">YEAR MODEL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $existing_units = $edit_units ?? [];
                                    for ($i = 0; $i < 20; $i++):
                                        $u = $existing_units[$i] ?? null;
                                    ?>
                                    <tr class="<?php echo $i % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                                        <td class="px-3 py-2 text-gray-500"><?php echo $i + 1; ?></td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][make]"
                                                   value="<?php echo htmlspecialchars($u['make'] ?? ''); ?>"
                                                   class="w-full px-2 py-1 border rounded focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][motor_no]"
                                                   value="<?php echo htmlspecialchars($u['motor_no'] ?? ''); ?>"
                                                   class="w-full px-2 py-1 border rounded focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][chasis_no]"
                                                   value="<?php echo htmlspecialchars($u['chasis_no'] ?? ''); ?>"
                                                   class="w-full px-2 py-1 border rounded focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][plate_no]"
                                                   value="<?php echo htmlspecialchars($u['plate_no'] ?? ''); ?>"
                                                   class="w-full px-2 py-1 border rounded focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][year_model]"
                                                   value="<?php echo htmlspecialchars($u['year_model'] ?? ''); ?>"
                                                   class="w-full px-2 py-1 border rounded focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500">
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t print:hidden">
                        <button type="button"
                                onclick="printDecisionCase()"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100 shadow-sm flex items-center gap-2 mr-auto border-dashed">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                            <span>Print Form</span>
                        </button>
                        <button type="button"
                                onclick="closeCaseModal()"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                           Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg hover:from-yellow-600 hover:to-yellow-700 flex items-center gap-2 text-sm font-bold shadow-md">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span><?php echo $edit_case ? 'Update Case' : 'Save Franchise Case'; ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="pt-2"> <!-- Subtle padding for alignment -->
<?php
$totalCount = count($cases);
$expiredCount = 0;
$expiringSoonCount = 0;
$activeCount = 0;
$now = time();
$soon = strtotime('+1 year');

foreach ($cases as $c) {
    if (empty($c['expiry_date'])) {
        $activeCount++;
        continue;
    }
    $ts = strtotime($c['expiry_date']);
    if ($ts < $now) $expiredCount++;
    elseif ($ts <= $soon) $expiringSoonCount++;
    else $activeCount++;
}
?>
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6 mb-8 bg-white p-1 rounded-2xl">
            <!-- Left Side: Title & Stats -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <div>
                    <h3 class="text-xl font-black text-gray-800 tracking-tight">Franchise Directory</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Fleet Monitoring</p>
                </div>
                
                <!-- Compact Internal Stats Row -->
                <div class="flex items-center gap-1.5 bg-gray-50/80 p-1 rounded-2xl border border-gray-100 shadow-inner">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500 shadow-[0_0_5px_rgba(59,130,246,0.5)]"></div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-tight">Total</span>
                        <span class="text-xs font-black text-gray-800" id="stat-total-cases"><?php echo $totalCount; ?></span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-tight">Active</span>
                        <span class="text-xs font-black text-green-600"><?php echo $activeCount; ?></span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-tight">Soon</span>
                        <span class="text-xs font-black text-orange-600"><?php echo $expiringSoonCount; ?></span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-white rounded-xl shadow-sm border border-red-100">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-tight">Expired</span>
                        <span class="text-xs font-black text-red-600"><?php echo $expiredCount; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Search & Actions -->
            <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                <!-- Advanced Search Bar -->
                <div class="relative flex-grow sm:flex-grow-0 sm:min-w-[280px]">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" id="franchiseSearch" 
                           placeholder="Search Case #, Applicant, Plate..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 focus:bg-white transition-all shadow-sm"
                           onkeyup="filterFranchiseItems()">
                </div>
                
                <!-- Status Filter -->
                <div class="relative min-w-[140px]">
                    <select id="statusFilter" 
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 focus:bg-white transition-all shadow-sm appearance-none cursor-pointer pr-10"
                            onchange="filterFranchiseItems()">
                        <option value="all">All Status</option>
                        <option value="active">Active Only</option>
                        <option value="expiring">Expiring Soon</option>
                        <option value="expired">Expired Only</option>
                    </select>
                    <i data-lucide="filter" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                </div>

                <button onclick="openCaseModal()" class="px-5 py-2.5 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl hover:from-yellow-600 hover:to-yellow-700 flex items-center gap-2 text-sm font-bold shadow-md transition-all shrink-0">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span>New Case</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">CASE NO.</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Applicant</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Type</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Denomination</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Date Filed</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Expiry Date</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600"># Units</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cases)): ?>
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-gray-500 bg-white">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="folder-open" class="w-10 h-10 text-gray-300 mb-2"></i>
                                    <p>No franchise cases found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: 
                        // Group cases by Expiry Date for better organization
                        $groupedCases = [];
                        foreach ($cases as $c) {
                            $expiry = empty($c['expiry_date']) ? 'No Expiry Date Set' : date('F j, Y', strtotime($c['expiry_date']));
                            $groupedCases[$expiry][] = $c;
                        }

                        // Sort the groups by date, pushing 'No Expiry Date' to the end or beginning
                        uksort($groupedCases, function($a, $b) {
                            if ($a === 'No Expiry Date Set') return 1;
                            if ($b === 'No Expiry Date Set') return -1;
                            return strtotime($a) - strtotime($b);
                        });
                    ?>
                        <?php foreach ($groupedCases as $expiry => $group): 
                            // Calculate status relative to today
                            $isExpired = false;
                            $isExpiringSoon = false;
                            if ($expiry !== 'No Expiry Date Set') {
                                $ts = strtotime($expiry);
                                $now = time();
                                if ($ts < $now) {
                                    $isExpired = true;
                                } elseif ($ts <= strtotime('+1 year')) {
                                    $isExpiringSoon = true;
                                }
                            }
                            
                            // Determine styles based on status
                            $bgGradient = 'from-yellow-50 to-white';
                            $borderColor = 'border-yellow-200';
                            $iconBg = 'bg-yellow-100';
                            $iconColor = 'text-yellow-700';
                            $textColor = 'text-yellow-600';
                            $badgeColor = 'border-yellow-200 text-yellow-700';
                            $animation = '';
                            $statusLabel = '';

                            if ($isExpired) {
                                $bgGradient = 'from-red-100 to-white';
                                $borderColor = 'border-red-400';
                                $iconBg = 'bg-red-200';
                                $iconColor = 'text-red-700';
                                $textColor = 'text-red-600';
                                $badgeColor = 'border-red-400 text-red-800 bg-red-50';
                                $animation = 'animate-pulse shadow-[0_0_15px_rgba(239,68,68,0.5)]'; // Blinking red glow effect
                                $statusLabel = '<span class="ml-3 px-2 py-0.5 text-[10px] uppercase font-black bg-red-600 text-white rounded animate-bounce">EXPIRED</span>';
                            } elseif ($isExpiringSoon) {
                                $bgGradient = 'from-orange-100 to-white';
                                $borderColor = 'border-orange-300';
                                $iconBg = 'bg-orange-200';
                                $iconColor = 'text-orange-700';
                                $textColor = 'text-orange-600';
                                $badgeColor = 'border-orange-300 text-orange-800 bg-orange-50';
                                $statusLabel = '<span class="ml-3 px-2 py-0.5 text-[10px] uppercase font-bold bg-orange-500 text-white rounded">RENEWAL ADVANCE ALERT</span>';
                            }
                        ?>
                            <!-- Group Header for Expiry Date -->
                            <tr class="bg-gradient-to-r <?php echo $bgGradient; ?> border-y <?php echo $borderColor; ?> <?php echo $isExpired ? 'relative z-10' : ''; ?> group-header" data-expiry-group="<?php echo htmlspecialchars($expiry); ?>">
                                <td colspan="8" class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 <?php echo $iconBg; ?> <?php echo $iconColor; ?> rounded my-1 shadow-sm border <?php echo $borderColor; ?> <?php echo $animation; ?>">
                                            <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center">
                                                <span class="text-xs <?php echo $textColor; ?> font-bold uppercase tracking-wider">EXPIRY DATE</span>
                                                <?php echo $statusLabel; ?>
                                            </div>
                                            <span class="text-base font-black text-gray-900"><?php echo htmlspecialchars($expiry); ?></span>
                                        </div>
                                        <div class="ml-auto flex items-center gap-2">
                                            <span class="px-3 py-1 text-xs font-bold bg-white border <?php echo $badgeColor; ?> rounded-full shadow-sm">
                                                <?php echo count($group); ?> <?php echo count($group) === 1 ? 'Case' : 'Cases'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                             <!-- Display Cases under this Expiry Date -->
                            <?php foreach ($group as $c): 
                                $statusKey = 'active';
                                if ($isExpired) $statusKey = 'expired';
                                elseif ($isExpiringSoon) $statusKey = 'expiring';
                                
                                // Collect all plate numbers for this case to make them searchable
                                $plates = implode(' ', array_column($c['units'] ?? [], 'plate_no'));
                            ?>
                                <tr class="border-b transition-colors hover:bg-yellow-50/50 cursor-pointer bg-white franchise-row"
                                    data-case-no="<?php echo strtolower($c['case_no']); ?>"
                                    data-applicant="<?php echo strtolower($c['applicant_name']); ?>"
                                    data-plates="<?php echo strtolower($plates); ?>"
                                    data-status="<?php echo $statusKey; ?>"
                                    data-expiry-group="<?php echo htmlspecialchars($expiry); ?>"
                                    onclick="document.getElementById('units-<?php echo $c['id']; ?>').classList.toggle('hidden')">
                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($c['case_no']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="font-bold text-blue-700"><?php echo htmlspecialchars($c['applicant_name']); ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($c['type_of_application']); ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($c['denomination']); ?></td>
                                    <td class="px-4 py-3 text-xs text-gray-500 font-medium whitespace-nowrap">
                                        <?php echo !empty($c['date_filed']) ? date('M d, Y', strtotime($c['date_filed'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-4 py-3 text-xs font-bold text-gray-700 whitespace-nowrap">
                                        <?php echo !empty($c['expiry_date']) ? date('M d, Y', strtotime($c['expiry_date'])) : '---'; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-700 font-bold text-xs ring-1 ring-gray-200">
                                            <?php echo (int)($c['unit_count'] ?? 0); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="px-3 py-1.5 text-[10px] uppercase font-bold tracking-wider bg-blue-50 text-blue-700 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors inline-block whitespace-nowrap">
                                                <i data-lucide="chevron-down" class="w-3 h-3 inline-block"></i> Units
                                            </button>
                                            <span onclick="event.stopPropagation(); window.location.href='<?php echo base_url('decision-management?id=' . $c['id']); ?>'" class="px-3 py-1.5 text-[10px] uppercase font-bold tracking-wider bg-gray-50 text-gray-700 border border-gray-200 rounded-md hover:bg-gray-100 transition-colors inline-block whitespace-nowrap cursor-pointer">
                                                Edit
                                            </span>
                                            <form method="POST" onsubmit="event.stopPropagation(); return confirm('Are you sure you want to completely delete this franchise case?');">
                                                <input type="hidden" name="action" value="delete_case">
                                                <input type="hidden" name="case_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete Case">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Dropdown Units Row -->
                                <tr id="units-<?php echo $c['id']; ?>" class="hidden bg-slate-50 border-b-2 border-slate-200 shadow-inner">
                                    <td colspan="8" class="p-4">
                                        <div class="pl-8 border-l-4 border-yellow-400">
                                            <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Attached Units (<?php echo count($c['units'] ?? []); ?>)</h4>
                                            
                                            <?php if(empty($c['units'])): ?>
                                                <p class="text-sm text-gray-500 italic py-2">No units attached to this case.</p>
                                            <?php else: ?>
                                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                                    <table class="min-w-full text-xs text-left">
                                                        <thead class="bg-gray-100 text-gray-600">
                                                            <tr>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider">MAKE</th>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider">MOTOR NO.</th>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider">CHASSIS NO.</th>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider">PLATE NO.</th>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider text-center">YEAR</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            <?php foreach($c['units'] as $u): ?>
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($u['make'] ?? ''); ?></td>
                                                                <td class="px-4 py-2 text-gray-700 font-mono"><?php echo htmlspecialchars($u['motor_no'] ?? ''); ?></td>
                                                                <td class="px-4 py-2 text-gray-700 font-mono"><?php echo htmlspecialchars($u['chasis_no'] ?? ''); ?></td>
                                                                <td class="px-4 py-2 text-blue-700 font-black tracking-wider"><?php echo htmlspecialchars($u['plate_no'] ?? ''); ?></td>
                                                                <td class="px-4 py-2 text-gray-900 font-bold text-center"><?php echo htmlspecialchars($u['year_model'] ?? ''); ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

<script>
function openCaseModal() {
    // If we're on an edit URL, we'll want to clear the URL effectively if opening "New", 
    // but the easiest is just redirecting to base to avoid messy state
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('id')) {
        window.location.href = '<?php echo base_url('decision-management'); ?>';
        return;
    }
    document.getElementById('caseModal').classList.remove('hidden');
    document.getElementById('caseModal').classList.add('flex');
}

function closeCaseModal() {
    // If we were editing, redirect back so modal doesn't open
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('id')) {
        window.location.href = '<?php echo base_url('decision-management'); ?>';
    } else {
        document.getElementById('caseModal').classList.add('hidden');
        document.getElementById('caseModal').classList.remove('flex');
    }
}

function printDecisionCase() {
    const area = document.getElementById('decisionPrintArea');
    if (!area) {
        window.print();
        return;
    }

    const printContents = area.innerHTML;
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        window.print();
        return;
    }

    printWindow.document.write(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decision Case Print</title>
    <style>
        body { background-color: #fff; padding: 24px; font-family: system-ui, sans-serif; }
        .print\\:hidden { display: none !important; }
        input { border: none !important; background: transparent !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div style="max-width: 900px; margin: 0 auto; text-align: center; margin-bottom: 20px;">
        <h2>FRANCHISE CASE DETAILS</h2>
    </div>
    <div style="max-width: 900px; margin: 0 auto;">
        ${printContents}
    </div>
</body>
</html>`);

    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => { printWindow.print(); }, 200);
}

// Ensure the modal works correctly with esc key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeCaseModal();
    }
});

function filterFranchiseItems() {
    const query = document.getElementById('franchiseSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.franchise-row');
    const headers = document.querySelectorAll('.group-header');
    
    // Track which groups have visible items
    const visibleGroups = new Set();
    let visibleCount = 0;

    rows.forEach(row => {
        const caseNo = row.dataset.caseNo;
        const applicant = row.dataset.applicant;
        const plates = row.dataset.plates;
        const status = row.dataset.status;
        const group = row.dataset.expiryGroup;
        const unitDetailsRow = document.getElementById(`units-${row.id.split('-')[1]}`);

        const matchesQuery = caseNo.includes(query) || applicant.includes(query) || plates.includes(query);
        const matchesStatus = statusFilter === 'all' || status === statusFilter;

        if (matchesQuery && matchesStatus) {
            row.classList.remove('hidden');
            visibleGroups.add(group);
            visibleCount++;
        } else {
            row.classList.add('hidden');
            // Also hide the detail row if open
            const detailID = row.getAttribute('onclick').match(/units-(\d+)/)[1];
            const detailRow = document.getElementById(`units-${detailID}`);
            if (detailRow) detailRow.classList.add('hidden');
        }
    });

    // Show/Hide headers based on visible items
    headers.forEach(header => {
        const groupName = header.dataset.expiryGroup;
        if (visibleGroups.has(groupName)) {
            header.classList.remove('hidden');
        } else {
            header.classList.add('hidden');
        }
    });

    // Update total count badge if it exists
    const totalBadge = document.getElementById('stat-total-cases');
    if (totalBadge) totalBadge.textContent = visibleCount;
}
</script>

</div>
@endsection
