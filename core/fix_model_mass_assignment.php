<?php
/**
 * CRITICAL MODEL SECURITY FIX
 * 
 * This script adds mass assignment protection to all unprotected models.
 * Run this script from the command line:
 * 
 * php fix_model_mass_assignment.php
 * 
 * Or run in tinker:
 * php artisan tinker < fix_model_mass_assignment.php
 */

$modelsPath = __DIR__ . '/app/Models';
$models = [
    'Admin',
    'AdminNotification', // Already has guarded
    'AdminPasswordReset', // Already has guarded
    'Announcement', // Already has guarded
    'AudioPlayer', // Already has guarded
    'CommissionLog', // Already has guarded
    'DailySpotlight', // Already has guarded
    'Deposit',
    'Extension',
    'Faq', // Already has guarded
    'Form',
    'Frontend',
    'Gateway',
    'GatewayCurrency',
    'GeneralSetting',
    'HoldWalletTransaction', // Already has guarded
    'Language',
    'NotificationLog',
    'NotificationTemplate',
    'Page',
    'PasswordReset',
    'Plan',
    'Ptc',
    'PtcView',
    'RedPack', // Already has fillable
    'RedPackAllocation', // Already has fillable
    'RedPackClaim', // Already has fillable
    'RedPackShare', // Already has fillable
    'RedPackTask', // Already has fillable
    'RedPackTaskCompletion', // Already has fillable
    'Referral', // Already has guarded
    'SupportAttachment',
    'SupportMessage',
    'SupportTicket',
    'Transaction', // Already has guarded
    'User', // Already has fillable
    'UserLogin',
    'UserNotification', // Already has guarded
    'VideoTutorial', // Already has guarded
    'VipTask', // Already has guarded
    'VipTaskCompletion', // Already has guarded
    'Withdrawal',
    'WithdrawMethod',
];

$modelsNeedingFix = [
    'Admin',
    'Deposit',
    'Extension',
    'Form',
    'Frontend',
    'Gateway',
    'GatewayCurrency',
    'GeneralSetting',
    'Language',
    'NotificationLog',
    'NotificationTemplate',
    'Page',
    'PasswordReset',
    'Plan',
    'Ptc',
    'PtcView',
    'SupportAttachment',
    'SupportMessage',
    'SupportTicket',
    'UserLogin',
    'Withdrawal',
    'WithdrawMethod',
];

echo "===========================================\n";
echo "MASS ASSIGNMENT PROTECTION FIX\n";
echo "===========================================\n\n";

foreach ($modelsNeedingFix as $model) {
    $filePath = $modelsPath . '/' . $model . '.php';
    
    if (!file_exists($filePath)) {
        echo "⚠️  SKIPPED: {$model}.php - File not found\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Check if already protected
    if (str_contains($content, '$guarded') || str_contains($content, '$fillable')) {
        echo "✅ OK: {$model}.php - Already protected\n";
        continue;
    }
    
    // Find the class declaration
    $pattern = '/class\s+' . $model . '\s+extends\s+\w+\s*\{/';
    
    if (preg_match($pattern, $content, $matches)) {
        // Add guarded property after class opening brace
        $replacement = $matches[0] . "\n    /**\n     * The attributes that aren't mass assignable.\n     *\n     * @var array\n     */\n    protected \$guarded = ['id'];\n";
        
        $newContent = preg_replace($pattern, $replacement, $content, 1);
        
        // Write the file
        file_put_contents($filePath, $newContent);
        echo "🔧 FIXED: {$model}.php - Added \$guarded = ['id']\n";
    } else {
        echo "⚠️  WARNING: {$model}.php - Could not find class declaration\n";
    }
}

echo "\n===========================================\n";
echo "VERIFICATION COMPLETE\n";
echo "===========================================\n";
echo "\nPlease test the application thoroughly after this fix.\n";
echo "All models should now reject mass assignment of the 'id' field.\n";
