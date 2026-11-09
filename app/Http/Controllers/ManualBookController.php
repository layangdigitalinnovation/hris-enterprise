<?php

namespace App\Http\Controllers;

use App\Helpers\ArabicPdfHelper;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ManualBookController extends Controller
{
    /**
     * Display the comprehensive interactive manual book portal.
     */
    public function index(Request $request)
    {
        $currentRole = auth()->user()->role ?? 'employee';
        $selectedTab = $request->input('tab', 'all');
        $searchQuery = $request->input('q', '');

        return view('manual-book.index', compact('currentRole', 'selectedTab', 'searchQuery'));
    }

    /**
     * Generate and download the official comprehensive manual book PDF.
     */
    public function downloadPdf(Request $request)
    {
        if ($request->has('lang')) {
            $lang = $request->input('lang');
            if (in_array($lang, ['id', 'ar'], true)) {
                app()->setLocale($lang);
                Carbon::setLocale($lang);
            }
        }

        $isAr = app()->getLocale() === 'ar';

        $companyName = SystemSetting::get('company_name', $isAr ? 'شركة لايانغ للابتكار الرقمي' : 'PT Layang Digital Innovation');
        $companyAddress = SystemSetting::get('company_address', $isAr ? 'الرياض (المملكة العربية السعودية) & جاكرتا' : 'Jakarta & Riyadh');
        $companyPhone = SystemSetting::get('company_phone', '+62 21 555-0199 / +966 11 000 0000');
        $version = 'v2.4 Enterprise';
        $generatedAt = Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y - H:i');
        $printedBy = auth()->user()->name ?? ($isAr ? 'مستخدم النظام' : 'User HRIS');

        $pdf = ArabicPdfHelper::loadView('manual-book.pdf', compact(
            'companyName',
            'companyAddress',
            'companyPhone',
            'version',
            'generatedAt',
            'printedBy',
            'isAr'
        ), 'a4', 'portrait');

        $filename = ($isAr ? 'Buku_Panduan_HRIS_AR_' : 'Buku_Panduan_HRIS_Enterprise_Pro_') . date('Ymd_His') . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}

