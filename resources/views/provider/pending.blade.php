@extends('layouts.provider')
@section('title', 'طلب التسجيل قيد المراجعة')
@section('body')
<div class="row justify-content-center py-5">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="panel p-4 p-lg-5 text-center">
            <div class="display-5 text-warning mb-3">⌛</div>
            <h1 class="h4 fw-bold">طلبك قيد مراجعة الإدارة</h1>
            <p class="text-secondary">سيتم تفعيل الحساب بعد اعتماد بيانات الشركة والمرفقات من الإدارة.</p>
            <div class="fw-semibold mb-4">{{ $profile->trade_name ?: $profile->company_name }}</div>
            <form method="post" action="{{ route('provider.logout') }}">
                @csrf
                <button class="btn btn-outline-secondary" type="submit">تسجيل الخروج</button>
            </form>
        </div>
    </div>
</div>
@endsection
