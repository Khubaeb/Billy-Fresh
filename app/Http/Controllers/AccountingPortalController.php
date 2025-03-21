<?php

namespace App\Http\Controllers;

use App\Models\AccountingDocument;
use App\Models\AccountingExport;
use App\Models\AccountingSettings;
use App\Models\Business;
use App\Models\ExportTemplate;
use App\Models\Expense;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccountingPortalController extends Controller
{
    /**
     * Display the accounting portal dashboard.
     */
    public function index(Request $request)
    {
        $business = $this->getCurrentBusiness();
        $accountingSettings = $this->getAccountingSettings($business);

        return view('accounting.index', [
            'business' => $business,
            'accountingSettings' => $accountingSettings,
        ]);
    }

    /**
     * Display the accounting options page.
     */
    public function options(Request $request)
    {
        $business = $this->getCurrentBusiness();

        return view('accounting.options', [
            'business' => $business,
        ]);
    }

    /**
     * Generate an income statement.
     */
    public function incomeStatement(Request $request)
    {
        $business = $this->getCurrentBusiness();
        $period = $request->input('period', 'month');
        $year = $request->input('year', date('Y'));
        $months = $request->input('months', date('n') > 1 ? (date('n') - 1) . '-' . date('n') : '1-' . date('n'));
        
        list($startMonth, $endMonth) = explode('-', $months);
        
        $startDate = date("Y-m-d", strtotime("$year-$startMonth-01"));
        $endDate = date("Y-m-t", strtotime("$year-$endMonth-01"));

        // Get revenue (invoices)
        $revenue = Invoice::where('business_id', $business->id)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get()
            ->groupBy(function($invoice) {
                return $invoice->invoice_date->format('m/Y');
            });

        // Get expenses
        $expenses = Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get()
            ->groupBy(function($expense) {
                return $expense->expense_date->format('m/Y');
            });

        return view('accounting.income_statement', [
            'business' => $business,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'period' => $period,
            'year' => $year,
            'months' => $months,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Display the account card report.
     */
    public function accountCard(Request $request)
    {
        $business = $this->getCurrentBusiness();
        
        return view('accounting.account_card', [
            'business' => $business,
        ]);
    }

    /**
     * Display the VAT payments report.
     */
    public function vatPayments(Request $request)
    {
        $business = $this->getCurrentBusiness();
        
        return view('accounting.vat_payments', [
            'business' => $business,
        ]);
    }

    /**
     * Display the profit and loss report.
     */
    public function profitLoss(Request $request)
    {
        $business = $this->getCurrentBusiness();
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        return view('accounting.profit_loss', [
            'business' => $business,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Display the advanced payments report.
     */
    public function advancedPayments(Request $request)
    {
        $business = $this->getCurrentBusiness();
        
        return view('accounting.advanced_payments', [
            'business' => $business,
        ]);
    }

    /**
     * Display the centralized card printing page.
     */
    public function centralizedCard(Request $request)
    {
        $business = $this->getCurrentBusiness();
        
        return view('accounting.centralized_card', [
            'business' => $business,
        ]);
    }

    /**
     * Display the accounting settings page.
     */
    public function settings(Request $request)
    {
        $business = $this->getCurrentBusiness();
        $accountingSettings = $this->getAccountingSettings($business);
        
        return view('accounting.settings', [
            'business' => $business,
            'accountingSettings' => $accountingSettings,
            'softwareOptions' => AccountingSettings::getAccountingSoftwareList(),
            'exportFrequencyOptions' => AccountingSettings::getExportFrequencyList(),
        ]);
    }

    /**
     * Update the accounting settings.
     */
    public function updateSettings(Request $request)
    {
        $business = $this->getCurrentBusiness();
        $accountingSettings = $this->getAccountingSettings($business);
        
        $validated = $request->validate([
            'accounting_office_name' => 'nullable|string|max:255',
            'accounting_contact_number' => 'nullable|string|max:255',
            'accounting_email' => 'nullable|email|max:255',
            'accounting_software' => 'nullable|string|max:255',
            'export_format_preference' => 'required|string|max:255',
            'include_attachments' => 'boolean',
            'auto_export_enabled' => 'boolean',
            'auto_export_frequency' => 'nullable|required_if:auto_export_enabled,1|string|max:255',
        ]);
        
        $accountingSettings->fill($validated);
        $accountingSettings->save();
        
        return redirect()->route('accounting.settings')
            ->with('success', 'Accounting settings updated successfully');
    }

    /**
     * Generate and download accounting materials.
     */
    public function downloadMaterials(Request $request)
    {
        $business = $this->getCurrentBusiness();
        $exportType = $request->input('export_type', AccountingExport::TYPE_INCOME_STATEMENT);
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        $format = $request->input('format', AccountingExport::FORMAT_PDF);
        
        // Create a new export job
        $export = new AccountingExport([
            'business_id' => $business->id,
            'user_id' => Auth::id(),
            'export_type' => $exportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'format' => $format,
            'status' => AccountingExport::STATUS_PENDING,
        ]);
        
        $export->save();
        
        // Process the export (in a real app, this might be handled by a queue)
        $this->processExport($export);
        
        if ($export->status === AccountingExport::STATUS_COMPLETED) {
            // Return the file for download
            return response()->download(
                storage_path('app/' . $export->file_path),
                $this->getExportFileName($export)
            );
        }
        
        return back()->with('error', 'Failed to generate export');
    }

    /**
     * Process the export and generate the file.
     */
    protected function processExport(AccountingExport $export)
    {
        $export->markAsProcessing();
        
        try {
            // Based on export type, generate the appropriate file
            switch ($export->export_type) {
                case AccountingExport::TYPE_INCOME_STATEMENT:
                    $filePath = $this->generateIncomeStatementFile($export);
                    break;
                case AccountingExport::TYPE_ACCOUNT_CARD:
                    $filePath = $this->generateAccountCardFile($export);
                    break;
                case AccountingExport::TYPE_VAT_PAYMENTS:
                    $filePath = $this->generateVatPaymentsFile($export);
                    break;
                case AccountingExport::TYPE_PROFIT_LOSS:
                    $filePath = $this->generateProfitLossFile($export);
                    break;
                case AccountingExport::TYPE_ADVANCED_PAYMENTS:
                    $filePath = $this->generateAdvancedPaymentsFile($export);
                    break;
                case AccountingExport::TYPE_CENTRALIZED_CARD:
                    $filePath = $this->generateCentralizedCardFile($export);
                    break;
                default:
                    throw new \Exception("Unsupported export type: {$export->export_type}");
            }
            
            $export->markAsCompleted($filePath);
        } catch (\Exception $e) {
            $export->markAsFailed();
            throw $e;
        }
    }

    /**
     * Generate an income statement file.
     */
    protected function generateIncomeStatementFile(AccountingExport $export)
    {
        // In a real app, this would generate a PDF/Excel/CSV file with income statement data
        // For now, we'll just create a placeholder file
        $business = Business::find($export->business_id);
        $year = date('Y', strtotime($export->start_date));
        $fileName = "income_statement_{$business->id}_{$year}_{$export->id}.{$export->format}";
        $filePath = "exports/{$fileName}";
        
        Storage::put($filePath, "Income Statement for {$business->name}\nPeriod: {$export->start_date} to {$export->end_date}");
        
        return $filePath;
    }

    /**
     * Generate an account card file.
     */
    protected function generateAccountCardFile(AccountingExport $export)
    {
        // Placeholder for account card generation
        $business = Business::find($export->business_id);
        $fileName = "account_card_{$business->id}_{$export->id}.{$export->format}";
        $filePath = "exports/{$fileName}";
        
        Storage::put($filePath, "Account Card for {$business->name}\nPeriod: {$export->start_date} to {$export->end_date}");
        
        return $filePath;
    }

    /**
     * Generate a VAT payments file.
     */
    protected function generateVatPaymentsFile(AccountingExport $export)
    {
        // Placeholder for VAT payments file generation
        $business = Business::find($export->business_id);
        $fileName = "vat_payments_{$business->id}_{$export->id}.{$export->format}";
        $filePath = "exports/{$fileName}";
        
        Storage::put($filePath, "VAT Payments for {$business->name}\nPeriod: {$export->start_date} to {$export->end_date}");
        
        return $filePath;
    }

    /**
     * Generate a profit and loss file.
     */
    protected function generateProfitLossFile(AccountingExport $export)
    {
        // Placeholder for profit and loss file generation
        $business = Business::find($export->business_id);
        $fileName = "profit_loss_{$business->id}_{$export->id}.{$export->format}";
        $filePath = "exports/{$fileName}";
        
        Storage::put($filePath, "Profit and Loss for {$business->name}\nPeriod: {$export->start_date} to {$export->end_date}");
        
        return $filePath;
    }

    /**
     * Generate an advanced payments file.
     */
    protected function generateAdvancedPaymentsFile(AccountingExport $export)
    {
        // Placeholder for advanced payments file generation
        $business = Business::find($export->business_id);
        $fileName = "advanced_payments_{$business->id}_{$export->id}.{$export->format}";
        $filePath = "exports/{$fileName}";
        
        Storage::put($filePath, "Advanced Payments for {$business->name}\nPeriod: {$export->start_date} to {$export->end_date}");
        
        return $filePath;
    }

    /**
     * Generate a centralized card file.
     */
    protected function generateCentralizedCardFile(AccountingExport $export)
    {
        // Placeholder for centralized card file generation
        $business = Business::find($export->business_id);
        $fileName = "centralized_card_{$business->id}_{$export->id}.{$export->format}";
        $filePath = "exports/{$fileName}";
        
        Storage::put($filePath, "Centralized Card for {$business->name}\nPeriod: {$export->start_date} to {$export->end_date}");
        
        return $filePath;
    }

    /**
     * Get the export file name.
     */
    protected function getExportFileName(AccountingExport $export)
    {
        $business = Business::find($export->business_id);
        $dateRange = date('Ymd', strtotime($export->start_date)) . '-' . date('Ymd', strtotime($export->end_date));
        
        $typeMap = [
            AccountingExport::TYPE_INCOME_STATEMENT => 'Income_Statement',
            AccountingExport::TYPE_ACCOUNT_CARD => 'Account_Card',
            AccountingExport::TYPE_VAT_PAYMENTS => 'VAT_Payments',
            AccountingExport::TYPE_PROFIT_LOSS => 'Profit_Loss',
            AccountingExport::TYPE_ADVANCED_PAYMENTS => 'Advanced_Payments',
            AccountingExport::TYPE_CENTRALIZED_CARD => 'Centralized_Card',
        ];
        
        $typeName = $typeMap[$export->export_type] ?? 'Export';
        
        return "{$typeName}_{$business->name}_{$dateRange}.{$export->format}";
    }

    /**
     * Export data to accounting software in a uniform structure.
     */
    public function exportToAccount(Request $request)
    {
        $business = $this->getCurrentBusiness();
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        // Create a new export job
        $export = new AccountingExport([
            'business_id' => $business->id,
            'user_id' => Auth::id(),
            'export_type' => 'uniform_export',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'format' => AccountingExport::FORMAT_UNIFORM,
            'status' => AccountingExport::STATUS_PENDING,
        ]);
        
        $export->save();
        
        // Process the export (in a real app, this might be handled by a queue)
        $this->processUniformExport($export);
        
        if ($export->status === AccountingExport::STATUS_COMPLETED) {
            // Return the file for download
            return response()->download(
                storage_path('app/' . $export->file_path),
                "Uniform_Export_{$business->name}_{$startDate}_{$endDate}.xml"
            );
        }
        
        return back()->with('error', 'Failed to generate export');
    }

    /**
     * Process the uniform export for accounting software.
     */
    protected function processUniformExport(AccountingExport $export)
    {
        $export->markAsProcessing();
        
        try {
            $business = Business::find($export->business_id);
            $accountingSettings = $this->getAccountingSettings($business);
            
            // Generate a uniform structure file (XML, JSON, etc.) based on accounting software
            $fileName = "uniform_export_{$business->id}_{$export->id}.xml";
            $filePath = "exports/{$fileName}";
            
            // Here we would generate the file in the format required by the accounting software
            // For now, we'll just create a placeholder XML file
            $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xmlContent .= '<AccountingExport>' . PHP_EOL;
            $xmlContent .= "  <Business>{$business->name}</Business>" . PHP_EOL;
            $xmlContent .= "  <StartDate>{$export->start_date}</StartDate>" . PHP_EOL;
            $xmlContent .= "  <EndDate>{$export->end_date}</EndDate>" . PHP_EOL;
            $xmlContent .= '  <Transactions>' . PHP_EOL;
            // Add placeholder transaction data
            $xmlContent .= '  </Transactions>' . PHP_EOL;
            $xmlContent .= '</AccountingExport>';
            
            Storage::put($filePath, $xmlContent);
            
            $export->markAsCompleted($filePath);
            return $filePath;
        } catch (\Exception $e) {
            $export->markAsFailed();
            throw $e;
        }
    }

    /**
     * Export accounting document index.
     */
    public function accountCardIndex(Request $request)
    {
        $business = $this->getCurrentBusiness();
        // This would generate an index of all accounting documents
        // For now, return a redirect to the accounting portal
        return redirect()->route('accounting.index')
            ->with('info', 'Account card index feature coming soon');
    }

    /**
     * Get the current business.
     */
    protected function getCurrentBusiness()
    {
        // Get the current user's business
        // In a multi-business system, this might involve more logic
        return Business::first();
    }

    /**
     * Get or create accounting settings for the business.
     */
    protected function getAccountingSettings(Business $business)
    {
        return AccountingSettings::firstOrCreate(
            ['business_id' => $business->id],
            [
                'export_format_preference' => AccountingExport::FORMAT_PDF,
                'include_attachments' => true,
                'auto_export_enabled' => false,
            ]
        );
    }
}
