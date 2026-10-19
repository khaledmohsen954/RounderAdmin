<section class="panel p-3 p-lg-4 mb-4">
<h2 class="h5">{{ __('Package comparison features') }}</h2>
<p class="text-secondary">{{ __('Define shared comparison rows, then set the value in each package.') }}</p>
<details class="mb-3"><summary class="fw-bold mb-3">{{ __('Add comparison feature') }}</summary>
@adminCan('admin.package-features.store','POST')<form method="post" action="{{ route('admin.package-features.store') }}">@csrf
@include('admin.packages.partials.feature-definition-fields',['feature'=>null])
</form>@endadminCan</details>
@foreach($featureDefinitions as $feature)
<details class="border rounded p-3 mb-2"><summary>{{ $feature->{'name_'.app()->getLocale()} }} <span class="text-secondary">#{{ $feature->id }} · {{ $feature->is_active ? __('Active') : __('Inactive') }}</span></summary>
@adminCan('admin.package-features.update','PUT')<form class="mt-3" method="post" action="{{ route('admin.package-features.update',$feature->id) }}">@csrf @method('PUT')
@include('admin.packages.partials.feature-definition-fields',['feature'=>$feature])
</form>@endadminCan</details>
@endforeach
</section>
