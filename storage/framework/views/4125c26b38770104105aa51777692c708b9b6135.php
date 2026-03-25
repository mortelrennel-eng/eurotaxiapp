<?php $__env->startSection('page-heading', 'Franchise Decision Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Toolbar / Actions -->
    <div class="flex justify-end">
        <button type="button"
                onclick="printDecisionCase()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100 shadow-sm">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Print</span>
        </button>
    </div>

    <div id="decisionPrintArea" class="space-y-6">
    <form method="POST" class="bg-white shadow rounded-lg p-6 space-y-6">
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

        <div class="flex justify-end gap-2 mt-4">
            <a href="<?php echo base_url('decision-management'); ?>"
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
               Clear / New Case
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span><?php echo $edit_case ? 'Update Case' : 'Save Case'; ?></span>
            </button>
        </div>
    </form>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Existing Cases</h3>
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
                            <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                No cases found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cases as $c): ?>
                            <tr class="border-t hover:bg-gray-50 cursor-pointer"
                                onclick="window.location.href='<?php echo base_url('decision-management?id=' . $c['id']); ?>'">
                                <td class="px-3 py-2"><?php echo htmlspecialchars($c['case_no']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($c['applicant_name']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($c['type_of_application']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($c['denomination']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($c['date_filed']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($c['expiry_date'] ?? ''); ?></td>
                                <td class="px-3 py-2 text-center"><?php echo (int)($c['unit_count'] ?? 0); ?></td>
                                <td class="px-3 py-2 flex gap-2">
                                    <span class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                        View / Edit
                                    </span>
                                    <form method="POST" onsubmit="event.stopPropagation(); return confirm('Delete this case?');">
                                        <input type="hidden" name="action" value="delete_case">
                                        <input type="hidden" name="case_id" value="<?php echo $c['id']; ?>">
                                        <button type="submit"
                                                class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

<script>
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
</head>
<body style="background-color: #ffffff; padding: 24px; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
    <div style="max-width: 900px; margin: 0 auto;">
        ${printContents}
    </div>
</body>
</html>`);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}
</script>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem\resources\views/decision-management/index.blade.php ENDPATH**/ ?>