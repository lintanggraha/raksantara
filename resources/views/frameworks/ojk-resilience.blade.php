@extends('layouts.tabler')

@section('title', 'Panduan Resiliensi OJK - Raksantara')

@push('styles')
<style>
.orj-page{--ink:#17324a;--muted:#5b6774;--line:rgba(23,50,74,.1);background:linear-gradient(180deg,#f5efe3 0%,#eef4f5 100%);border-radius:28px;padding:.25rem;color:var(--ink)}.orj-shell{display:grid;gap:1rem}.orj-hero{display:grid;grid-template-columns:1.4fr .9fr;gap:1rem;align-items:stretch;min-height:368px;padding:1.45rem;border-radius:24px;background:radial-gradient(circle at top right,rgba(241,214,168,.9),transparent 32%),radial-gradient(circle at bottom left,rgba(149,202,213,.68),transparent 28%),linear-gradient(135deg,#14314a 0%,#1f5e72 48%,#ead7b1 100%);box-shadow:0 18px 40px rgba(15,23,42,.08)}.orj-kicker{display:inline-flex;align-items:center;gap:.45rem;padding:.35rem .7rem;border-radius:999px;background:rgba(255,250,242,.18);color:#fffaf2;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.orj-title{margin:1rem 0 .7rem;color:#fffaf2;font-size:clamp(2rem,3.7vw,3rem);font-weight:800;line-height:1.05}.orj-lede{margin:0;max-width:760px;color:rgba(255,250,242,.84);line-height:1.72}.orj-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:.7rem;margin-top:1.1rem}.orj-metric,.orj-panel,.orj-side{border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,251,245,.98) 0%,rgba(246,251,252,.98) 100%);box-shadow:0 12px 24px rgba(15,23,42,.05)}.orj-metric{padding:.68rem .74rem;border-radius:16px;background:rgba(255,250,242,.14);border-color:rgba(255,255,255,.18);min-height:96px;display:flex;flex-direction:column;justify-content:flex-start}.orj-metric label,.orj-side label,.orj-form label,.orj-inspector small{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.orj-metric label{color:rgba(255,250,242,.74)}.orj-metric strong{display:block;margin-top:.25rem;color:#fffaf2;font-size:1.45rem;font-weight:800}.orj-metric span{display:block;margin-top:.25rem;color:rgba(255,250,242,.74);font-size:.76rem;line-height:1.45}.orj-side{padding:.8rem .86rem;border-radius:18px;background:rgba(255,250,242,.82);min-height:142px}.orj-side label{color:var(--muted);margin-bottom:.35rem}.orj-side h3{margin:0;font-size:1rem;font-weight:800}.orj-side p{margin:.5rem 0 0;color:var(--muted);font-size:.84rem;line-height:1.58}
.orj-nav{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem}.orj-tab{display:grid;grid-template-columns:auto 1fr;gap:.7rem;align-items:center;padding:.82rem .88rem;border-radius:18px;border:1px solid var(--line);background:linear-gradient(180deg,rgba(255,250,242,.94) 0%,rgba(239,245,246,.94) 100%);text-align:left;color:var(--ink);cursor:pointer;box-shadow:0 10px 22px rgba(15,23,42,.04)}.orj-tab.active{border-color:rgba(20,78,114,.24);box-shadow:0 16px 28px rgba(20,78,114,.08)}.orj-tab i{width:2.25rem;height:2.25rem;display:inline-flex;align-items:center;justify-content:center;border-radius:14px;background:rgba(20,38,59,.06)}.orj-tab strong{display:block;font-size:.9rem;font-weight:800}.orj-tab span{display:block;margin-top:.12rem;color:var(--muted);font-size:.76rem;line-height:1.4}
.orj-grid{display:grid;gap:1rem}.orj-grid.two{grid-template-columns:1.04fr .96fr}.orj-panel{padding:1rem;border-radius:20px}.orj-head{display:flex;align-items:center;justify-content:space-between;gap:.8rem;margin-bottom:.75rem}.orj-head h3{margin:0;font-size:.98rem;font-weight:800}.orj-chip,.orj-pill,.orj-meta span{display:inline-flex;align-items:center;gap:.3rem;padding:.24rem .52rem;border-radius:999px;background:rgba(20,38,59,.08);font-size:.7rem;font-weight:700}.orj-copy{margin:0 0 .8rem;color:var(--muted);line-height:1.62;font-size:.84rem}.orj-bars,.orj-cards,.orj-theme-groups,.orj-list{display:grid;gap:.65rem}.orj-bar,.orj-card,.orj-theme-link,.orj-item{width:100%;text-align:left;border:1px solid transparent;background:rgba(255,255,255,.74);cursor:pointer}.orj-bar{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(160px,1fr) auto;gap:.65rem;align-items:center;padding:.66rem .74rem;border-radius:16px}.orj-bar:hover,.orj-bar.active,.orj-card:hover,.orj-theme-link:hover,.orj-item.active{border-color:rgba(20,78,114,.22);box-shadow:0 12px 24px rgba(20,78,114,.08)}.orj-bar strong,.orj-card strong,.orj-theme-link strong,.orj-item-name{display:block;font-size:.86rem;font-weight:800}.orj-bar em,.orj-card p,.orj-theme-link em,.orj-item-meta{display:block;margin-top:.14rem;color:var(--muted);font-size:.74rem;font-style:normal;line-height:1.45}.orj-track{height:.42rem;border-radius:999px;background:rgba(20,38,59,.08);overflow:hidden}.orj-track b{display:block;height:100%}.orj-num{min-width:2rem;text-align:right;font-weight:800}.orj-cards{grid-template-columns:repeat(2,1fr)}.orj-card,.orj-theme-link{padding:.8rem;border-radius:16px;border-color:var(--line)}.orj-card-top{display:flex;align-items:center;justify-content:space-between;color:var(--muted);font-size:.66rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700}.orj-icon{width:1.85rem;height:1.85rem;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;background:rgba(20,78,114,.12);color:#144e72}
.orj-workspace{display:grid;grid-template-columns:.74fr .98fr .94fr;gap:1rem}.orj-form{display:grid;gap:.75rem}.orj-form label{margin-bottom:.3rem;color:var(--muted)}.orj-summary{margin-top:.85rem;padding:.82rem .88rem;border-radius:18px;background:linear-gradient(180deg,rgba(25,61,87,.95) 0%,rgba(20,78,114,.95) 100%);color:#fffaf2}.orj-summary small{display:block;color:rgba(255,250,242,.7);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.orj-summary strong{display:block;margin-top:.28rem;font-size:1.72rem;font-weight:800}.orj-summary span{display:block;margin-top:.4rem;color:rgba(255,250,242,.78);font-size:.78rem;line-height:1.5}.orj-list{max-height:720px;overflow-y:auto;padding-right:.12rem}.orj-item{position:relative;padding:.72rem .8rem .68rem .92rem;border-radius:14px;border-color:rgba(20,38,59,.08)}.orj-item::before{content:'';position:absolute;left:0;top:.68rem;bottom:.68rem;width:.18rem;border-radius:999px;background:var(--accent,#144e72)}.orj-item-top{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;justify-content:space-between}.orj-item-code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Courier New",monospace;font-size:.76rem;font-weight:800;color:var(--accent,#144e72)}.orj-inspector{position:sticky;top:1rem;min-height:720px;display:flex;flex-direction:column}.orj-inspector-head{padding-bottom:.85rem;border-bottom:1px solid var(--line)}.orj-inspector-head strong{display:block;margin-top:.35rem;font-size:1rem;font-weight:800;color:#144e72}.orj-inspector-head span{display:block;margin-top:.28rem;font-size:.9rem;font-weight:800;line-height:1.4}.orj-inspector-body{display:grid;gap:.75rem;padding-top:.85rem;flex:1;overflow:auto;align-content:start}.orj-meta{display:flex;flex-wrap:wrap;gap:.45rem}.orj-callout,.orj-note{padding:.76rem .84rem;border-radius:16px;border:1px solid var(--line);line-height:1.62}.orj-callout{background:rgba(255,255,255,.76)}.orj-note{background:rgba(238,245,245,.84)}.orj-plain{margin:.15rem 0 0;padding-left:1rem;color:var(--muted);font-size:.78rem;line-height:1.6}.orj-plain li{margin-bottom:.18rem}.orj-empty{padding:.9rem;border-radius:16px;border:1px dashed rgba(20,38,59,.18);background:rgba(255,255,255,.6);color:var(--muted);text-align:center;line-height:1.55}
@media (max-width:1399.98px){.orj-workspace{grid-template-columns:1fr}.orj-inspector{position:static;min-height:auto}}@media (max-width:1199.98px){.orj-hero,.orj-metric,.orj-side{min-height:auto}.orj-hero,.orj-nav,.orj-grid.two,.orj-metrics,.orj-cards,.orj-theme-groups{grid-template-columns:1fr}.orj-bar{grid-template-columns:1fr}}@media (max-width:767.98px){.orj-hero,.orj-panel{padding:1.2rem;border-radius:22px}}
</style>
@endpush

@section('content')
@php
    $guidanceCollection = collect($guidanceThemes);
    $totalThemes = $guidanceCollection->count();
    $totalSections = $guidanceCollection->pluck('section')->filter()->unique()->count();
    $totalFocusPoints = $guidanceCollection->sum(fn ($item) => count($item['focus'] ?? []));
    $sectionMeta = [
        'A. Latar Belakang' => ['label' => 'Latar Belakang', 'color' => '#2563eb', 'icon' => 'fa-compass', 'summary' => 'Menjelaskan konteks ketergantungan digital, eksposur gangguan, dan urgensi kesinambungan layanan.'],
        'B. Ketahanan Digital' => ['label' => 'Ketahanan Digital', 'color' => '#0f766e', 'icon' => 'fa-shield-alt', 'summary' => 'Menekankan kemampuan mencegah, mendeteksi, merespons, dan memulihkan gangguan pada layanan digital.'],
        'C. Kerangka Ketahanan Digital' => ['label' => 'Kerangka Ketahanan', 'color' => '#b45309', 'icon' => 'fa-sitemap', 'summary' => 'Menguatkan tata kelola, struktur pengelolaan, pengujian, dan peningkatan berkelanjutan.'],
    ];
    $sectionBreakdown = collect($sectionMeta)->map(function ($meta, $section) use ($guidanceCollection) {
        $items = $guidanceCollection->where('section', $section);
        return ['key' => $section, 'label' => $meta['label'], 'color' => $meta['color'], 'icon' => $meta['icon'], 'summary' => $meta['summary'], 'count' => $items->count()];
    })->filter(fn ($item) => $item['count'] > 0)->values();
    $maxSectionCount = max($sectionBreakdown->max('count') ?: 0, 1);
    $sectionOptions = $sectionBreakdown->pluck('key')->values();
    $themesBySection = $guidanceCollection->groupBy('section');
    $firstTheme = $guidanceCollection->first();
    $firstSection = $firstTheme ? ($sectionMeta[$firstTheme['section']] ?? null) : null;
@endphp

<div class="orj-page">
    <div class="orj-shell">
        <section class="orj-hero">
            <div>
                <span class="orj-kicker"><i class="fas fa-landmark"></i>Framework Studio</span>
                <h1 class="orj-title">Panduan Resiliensi OJK</h1>
                <p class="orj-lede">
                    Halaman ini menyajikan ringkasan Panduan Resiliensi Digital OJK dalam bentuk struktur dokumen,
                    explorer tema, dan fokus implementasi untuk membantu pembacaan materi ketahanan digital secara
                    lebih terstruktur dan operasional.
                </p>
                <div class="orj-metrics">
                    <div class="orj-metric"><label>Bagian Utama</label><strong>{{ $totalSections }}</strong><span>Latar belakang, ketahanan digital, dan kerangka ketahanan digital.</span></div>
                    <div class="orj-metric"><label>Tema Panduan</label><strong>{{ $totalThemes }}</strong><span>Tema prioritas yang dirangkum dari struktur dokumen panduan.</span></div>
                    <div class="orj-metric"><label>Fokus Implementasi</label><strong>{{ $totalFocusPoints }}</strong><span>Arahan implementasi yang dapat ditelaah satu per satu dalam explorer.</span></div>
                </div>
            </div>
            <div class="orj-grid">
                <div class="orj-side">
                    <label>Cakupan Panduan</label>
                    <h3>Fokus pada ketahanan layanan digital</h3>
                    <p>Panduan ditempatkan sebagai acuan untuk memahami konteks gangguan digital, kesiapan operasional, tata kelola, dan penguatan berkelanjutan dalam lingkungan layanan perbankan.</p>
                </div>
                <div class="orj-side">
                    <label>Arah Pemanfaatan</label>
                    <p>Pembacaan dapat dimulai dari struktur dokumen, dilanjutkan ke explorer tema untuk menelaah ringkasan dan fokus implementasi, lalu ditutup dengan board fokus per bagian untuk menyusun prioritas tindak lanjut.</p>
                </div>
            </div>
        </section>

        <div class="orj-nav nav" role="tablist">
            <button class="orj-tab active" data-bs-toggle="tab" data-bs-target="#orj-overview-pane" type="button" role="tab"><i class="fas fa-chart-line"></i><span><strong>Ringkasan Panduan</strong><span>Struktur dokumen, tema utama, dan sorotan implementasi.</span></span></button>
            <button class="orj-tab" data-bs-toggle="tab" data-bs-target="#orj-explorer-pane" type="button" role="tab"><i class="fas fa-sliders-h"></i><span><strong>Explorer Tema</strong><span>Filter tema, baca ringkasan, dan telaah fokus implementasi.</span></span></button>
            <button class="orj-tab" data-bs-toggle="tab" data-bs-target="#orj-focus-pane" type="button" role="tab"><i class="fas fa-layer-group"></i><span><strong>Fokus Implementasi</strong><span>Board tema per bagian untuk membantu penelaahan tindak lanjut.</span></span></button>
        </div>

        <div class="tab-content orj-grid">
            <div class="tab-pane fade show active" id="orj-overview-pane" role="tabpanel">
                <div class="orj-grid two">
                    <article class="orj-panel">
                        <div class="orj-head"><h3>Struktur bagian dokumen</h3><span class="orj-chip">{{ $totalSections }} bagian</span></div>
                        <p class="orj-copy">Pembacaan panduan dapat dimulai dari tiga bagian utama berikut untuk memahami konteks, kapabilitas ketahanan, dan penguatan kerangka pengelolaan ketahanan digital.</p>
                        <div class="orj-bars">
                            @foreach ($sectionBreakdown as $item)
                                <button type="button" class="orj-bar" data-section-jump="{{ $item['key'] }}"><span><strong>{{ $item['key'] }}</strong><em>{{ $item['summary'] }}</em></span><span class="orj-track"><b style="width: {{ round(($item['count'] / $maxSectionCount) * 100, 1) }}%; background: {{ $item['color'] }}"></b></span><span class="orj-num">{{ $item['count'] }}</span></button>
                            @endforeach
                        </div>
                    </article>
                    <article class="orj-panel">
                        <div class="orj-head"><h3>Tema utama panduan</h3><span class="orj-chip">{{ $totalThemes }} tema</span></div>
                        <p class="orj-copy">Setiap tema merangkum pokok arahan yang dapat dibaca lebih lanjut melalui explorer untuk memahami implikasi implementasi pada ketahanan digital.</p>
                        <div class="orj-cards">
                            @foreach ($guidanceThemes as $theme)
                                <button type="button" class="orj-card" data-theme-jump="{{ $theme['id'] }}"><div class="orj-card-top"><span class="orj-icon"><i class="fas {{ $sectionMeta[$theme['section']]['icon'] ?? 'fa-book-open' }}"></i></span><span>{{ $theme['focus_count'] ?? count($theme['focus'] ?? []) }} fokus</span></div><strong>{{ $theme['title'] }}</strong><p>{{ $theme['section'] }}</p></button>
                            @endforeach
                        </div>
                    </article>
                </div>
                <div class="orj-grid two">
                    <article class="orj-panel">
                        <div class="orj-head"><h3>Sorotan implementasi</h3><span class="orj-chip">Prioritas pembacaan</span></div>
                        <div class="orj-note">Panduan Resiliensi OJK menekankan pemahaman konteks layanan digital, pengelolaan gangguan, pemulihan layanan, dan penguatan tata kelola sebagai satu rangkaian ketahanan yang saling terhubung.</div>
                    </article>
                    <article class="orj-panel">
                        <div class="orj-head"><h3>Arah pemanfaatan</h3><span class="orj-chip">Pendekatan kerja</span></div>
                        <p class="orj-copy">Halaman ini dirancang untuk membantu pembacaan tematik ketika organisasi perlu memahami fokus prioritas, menyusun agenda penguatan, atau menelaah area kesiapan ketahanan digital secara lebih sistematis.</p>
                        <div class="orj-note">Klik salah satu bagian atau tema untuk langsung berpindah ke explorer dan membaca detail fokus implementasi dari tema yang dipilih.</div>
                    </article>
                </div>
            </div>

            <div class="tab-pane fade" id="orj-explorer-pane" role="tabpanel">
                <div class="orj-workspace">
                    <article class="orj-panel">
                        <div class="orj-head"><h3>Filter Tema</h3><span class="orj-chip">{{ $totalThemes }} tema</span></div>
                        <div class="orj-form">
                            <div><label for="sectionFilter">Bagian Dokumen</label><select id="sectionFilter" class="form-select"><option value="">Semua bagian</option>@foreach ($sectionOptions as $section)<option value="{{ $section }}">{{ $section }}</option>@endforeach</select></div>
                            <div><label for="themeSearch">Cari tema</label><input id="themeSearch" type="search" class="form-control" placeholder="Cari ID, judul, ringkasan, atau fokus implementasi"></div>
                            <button type="button" class="btn btn-outline-secondary" id="resetThemeFilters">Atur ulang filter</button>
                        </div>
                        <div class="orj-summary"><small>Tema Ditampilkan</small><strong id="themeVisibleCount">{{ $totalThemes }}</strong><span id="themeVisibleSummary">Menampilkan seluruh tema Panduan Resiliensi OJK yang dipetakan pada halaman ini.</span></div>
                    </article>
                    <article class="orj-panel">
                        <div class="orj-head"><h3>Daftar tema</h3><span class="orj-chip" id="themeCountChip">{{ $totalThemes }} entri</span></div>
                        <div id="themeList" class="orj-list"></div>
                    </article>
                    <article class="orj-panel orj-inspector">
                        <div class="orj-inspector-head"><small>Theme Inspector</small><strong id="theme-id">{{ $firstTheme['id'] ?? '-' }}</strong><span id="theme-title">{{ $firstTheme['title'] ?? 'Pilih tema untuk membaca detail.' }}</span></div>
                        <div class="orj-inspector-body">
                            <div class="orj-meta"><span id="theme-section">{{ $firstTheme['section'] ?? '-' }}</span><span id="theme-focus-count">{{ $firstTheme['focus_count'] ?? count($firstTheme['focus'] ?? []) }} fokus</span><span id="theme-section-label">{{ $firstSection['label'] ?? '-' }}</span></div>
                            <div class="orj-callout"><strong>Ringkasan Tema</strong><div id="theme-summary" class="mt-2">{{ $firstTheme['summary'] ?? 'Pilih tema untuk membaca ringkasan.' }}</div></div>
                            <div class="orj-note"><strong>Interpretasi Implementasi</strong><div id="theme-interpretation" class="mt-2">{{ $firstTheme['interpretation'] ?? '-' }}</div></div>
                            <div class="orj-callout"><strong>Fokus Implementasi</strong><ul id="theme-focus" class="orj-plain">@forelse (($firstTheme['focus'] ?? []) as $item)<li>{{ $item }}</li>@empty<li>Tidak ada fokus implementasi tambahan.</li>@endforelse</ul></div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="tab-pane fade" id="orj-focus-pane" role="tabpanel">
                <div class="orj-theme-groups">
                    @foreach ($sectionBreakdown as $item)
                        <article class="orj-panel">
                            <div class="orj-head"><h3>{{ $item['key'] }}</h3><span class="orj-chip">{{ $item['count'] }} tema</span></div>
                            <p class="orj-copy">{{ $item['summary'] }}</p>
                            <div class="orj-bars">
                                @foreach (($themesBySection[$item['key']] ?? collect()) as $theme)
                                    <button type="button" class="orj-theme-link" data-theme-jump="{{ $theme['id'] }}"><strong>{{ $theme['title'] }}</strong><em>{{ $theme['focus_count'] ?? count($theme['focus'] ?? []) }} fokus implementasi</em></button>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){const themes=@json($guidanceThemes);const sectionMeta=@json($sectionMeta);const themeMap=Object.fromEntries(themes.map(item=>[item.id,item]));const firstThemeId=@json($firstTheme['id']??null);const explorerTab=document.querySelector('.orj-tab[data-bs-target="#orj-explorer-pane"]');const sectionFilter=document.getElementById('sectionFilter');const themeSearch=document.getElementById('themeSearch');const themeList=document.getElementById('themeList');let activeThemeId=firstThemeId;const showTab=trigger=>trigger&&bootstrap.Tab.getOrCreateInstance(trigger).show();function syncTabState(activeBtn){document.querySelectorAll('.orj-tab').forEach(tab=>tab.classList.remove('active'));if(activeBtn)activeBtn.classList.add('active')}function syncSectionState(){document.querySelectorAll('[data-section-jump]').forEach(button=>button.classList.toggle('active',button.dataset.sectionJump===sectionFilter.value&&sectionFilter.value!==''))}function renderThemeInspector(theme){if(!theme){document.getElementById('theme-id').textContent='-';document.getElementById('theme-title').textContent='Tidak ada tema yang sesuai dengan filter aktif.';document.getElementById('theme-section').textContent='-';document.getElementById('theme-focus-count').textContent='0 fokus';document.getElementById('theme-section-label').textContent='-';document.getElementById('theme-summary').textContent='Sesuaikan filter untuk melihat tema yang tersedia.';document.getElementById('theme-interpretation').textContent='-';document.getElementById('theme-focus').innerHTML='<li>Tidak ada tema yang cocok.</li>';return}const section=sectionMeta[theme.section]||{label:theme.section||'-'};document.getElementById('theme-id').textContent=theme.id||'-';document.getElementById('theme-title').textContent=theme.title||'-';document.getElementById('theme-section').textContent=theme.section||'-';document.getElementById('theme-focus-count').textContent=`${theme.focus_count||(theme.focus||[]).length} fokus`;document.getElementById('theme-section-label').textContent=section.label||'-';document.getElementById('theme-summary').textContent=theme.summary||'-';document.getElementById('theme-interpretation').textContent=theme.interpretation||'-';const focus=document.getElementById('theme-focus');focus.innerHTML='';(theme.focus&&theme.focus.length?theme.focus:['Tidak ada fokus implementasi tambahan.']).forEach(item=>{const li=document.createElement('li');li.textContent=item;focus.appendChild(li)})}function buildThemeItem(theme,selected){const section=sectionMeta[theme.section]||{label:theme.section||'-',color:'#144e72'};return `<button type="button" class="orj-item${selected?' active':''}" data-theme-id="${theme.id}" style="--accent:${section.color}"><div class="orj-item-top"><span class="orj-item-code">${theme.id}</span><span class="orj-pill">${section.label}</span></div><div class="orj-item-name">${theme.title||'-'}</div><div class="orj-item-meta"><span>${theme.section||'-'}</span><span>${theme.focus_count||(theme.focus||[]).length} fokus</span></div></button>`}function renderThemeList(list){if(!list.length){themeList.innerHTML='<div class="orj-empty">Tidak ada tema yang cocok dengan filter saat ini.</div>';return}themeList.innerHTML=list.map(item=>buildThemeItem(item,item.id===activeThemeId)).join('');themeList.querySelectorAll('.orj-item').forEach(node=>{node.addEventListener('click',function(){const id=node.dataset.themeId;if(id){setActiveTheme(id);renderThemeList(list)}})})}function setActiveTheme(id){activeThemeId=id;renderThemeInspector(themeMap[id]||null);document.querySelectorAll('.orj-item').forEach(item=>item.classList.toggle('active',item.dataset.themeId===id))}function updateThemeSummary(count){document.getElementById('themeVisibleCount').textContent=count;document.getElementById('themeCountChip').textContent=`${count} entri`;document.getElementById('themeVisibleSummary').textContent=count?`Menampilkan ${count} tema Panduan Resiliensi OJK sesuai filter aktif.`:'Tidak ada tema yang cocok dengan filter saat ini.'}function filteredThemes(){const query=(themeSearch.value||'').trim().toLowerCase();return themes.filter(theme=>{if(sectionFilter.value&&theme.section!==sectionFilter.value)return false;if(!query)return true;return[theme.id,theme.title,theme.section,theme.summary,theme.interpretation,...(theme.focus||[])].join(' ').toLowerCase().includes(query)})}function applyThemeFilters(preferredId=null){const list=filteredThemes();updateThemeSummary(list.length);syncSectionState();if(!list.length){activeThemeId=null;renderThemeInspector(null);renderThemeList([]);return}const visibleIds=list.map(item=>item.id);const target=preferredId&&visibleIds.includes(preferredId)?preferredId:(activeThemeId&&visibleIds.includes(activeThemeId)?activeThemeId:visibleIds[0]);setActiveTheme(target);renderThemeList(list)}function jumpSection(section){sectionFilter.value=section||'';themeSearch.value='';applyThemeFilters();showTab(explorerTab)}function jumpTheme(id){if(!themeMap[id])return;sectionFilter.value='';themeSearch.value='';applyThemeFilters(id);showTab(explorerTab)}document.querySelectorAll('.orj-tab').forEach(button=>button.addEventListener('shown.bs.tab',()=>syncTabState(button)));document.querySelectorAll('[data-section-jump]').forEach(button=>button.addEventListener('click',function(){jumpSection(button.dataset.sectionJump||'')}));document.querySelectorAll('[data-theme-jump]').forEach(button=>button.addEventListener('click',function(){jumpTheme(button.dataset.themeJump||'')}));sectionFilter.addEventListener('change',()=>applyThemeFilters());themeSearch.addEventListener('input',()=>applyThemeFilters());document.getElementById('resetThemeFilters').addEventListener('click',function(){sectionFilter.value='';themeSearch.value='';applyThemeFilters(firstThemeId)});syncTabState(document.querySelector('.orj-tab.active'));applyThemeFilters(firstThemeId);});
</script>
@endpush
