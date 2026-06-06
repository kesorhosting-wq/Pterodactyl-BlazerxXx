<style>
	:root{--euph-overlay: rgba(6,8,12,0.62);--card-bg: rgba(8,10,14,0.72)}
	.euph-maintenance-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:48px;background-color:#06060a;background-repeat:no-repeat;background-size:cover;background-position:center;background-attachment:fixed}
	.euph-maintenance-card{width:100%;max-width:980px;background:var(--card-bg);border-radius:14px;padding:36px;box-shadow:0 18px 60px rgba(0,0,0,0.65);border:1px solid rgba(255,255,255,0.04);backdrop-filter:blur(10px);color:#ffffff}
	.euph-maintenance-inner{display:flex;gap:28px;align-items:center}
	.euph-maintenance-left{flex:0 0 140px;display:flex;flex-direction:column;align-items:center}
	.euph-maintenance-right{flex:1}
	.euph-maintenance-logo{width:96px;height:96px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:28px;color:#fff;margin-bottom:10px}
	.euph-maintenance-title{font-size:2rem;margin:0 0 8px 0}
	.euph-maintenance-sub{color:#cfd6ffcc;margin:0 0 14px 0;font-size:1rem;line-height:1.5}
	.euph-maintenance-actions{margin-top:18px}
	.btn-euph{display:inline-block;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:600;color:#fff;border:1px solid rgba(255,255,255,0.06);box-shadow:0 10px 30px rgba(88,102,255,0.12);}
	.btn-euph.secondary{background:transparent;border:1px solid rgba(255,255,255,0.06);color:#ddd}
	/* subtle overlay to make text readable over busy backgrounds */
	.euph-maintenance-wrap::before{content:'';position:fixed;inset:0;pointer-events:none}
	@media (max-width:920px){
		.euph-maintenance-inner{flex-direction:column;text-align:center}
		.euph-maintenance-left{flex:0 0 auto}
		.euph-maintenance-right{padding-top:8px}
		.euph-maintenance-logo{width:84px;height:84px;font-size:22px}
		.euph-maintenance-title{font-size:1.6rem}
	}
</style>

<?php
	// Attempt to read configured maintenance message and background from blueprint DB
	$maintenance_message = $blueprint->dbGet('euphoriatheme', 'maintenance_message', 'The site is currently in maintenance mode. We\'ll be back soon.');
	$maintenance_background_url = $blueprint->dbGet('euphoriatheme', 'maintenance_background_url', '');
	// Theme primary color and logo
	$primary_color = $blueprint->dbGet('euphoriatheme', 'primary_color', '#667eea');
	$logo_url = $blueprint->dbGet('euphoriatheme', 'logo_url', '/assets/extensions/euphoriatheme/logo.png');
	$site_name = config('app.name', 'Pterodactyl');
?>

<div class="euph-maintenance-wrap" @if(!empty($maintenance_background_url)) style="background-image: url('{{ e($maintenance_background_url) }}');" @endif>
	<div class="euph-maintenance-card">
		<div class="euph-maintenance-inner">
			<div class="euph-maintenance-left">
				<div class="euph-maintenance-logo">
					@if(!empty($logo_url))
						<img src="{{ e($logo_url) }}" alt="{{ e($site_name) }}" style="width:88px;height:88px;object-fit:cover;border-radius:10px;border:1px solid rgba(255,255,255,0.06);">
					@else
						<div style="font-size:28px;font-weight:700;color:#fff">{{ strtoupper(substr($site_name,0,1)) }}</div>
					@endif
				</div>
				<p style="margin:0;color:#aeb8ff80;font-size:0.9rem">{{ e($site_name) }}</p>
			</div>
			<div class="euph-maintenance-right">
				<h2 class="euph-maintenance-title">We'll be back shortly</h2>
				<p class="euph-maintenance-sub">{{ $maintenance_message }}</p>
				<div class="euph-maintenance-actions">
					<a class="btn-euph" href="/admin">Go to Admin</a>
					<a class="btn-euph secondary" href="/" style="margin-left:12px">Home</a>
					@if(!is_null(Auth::user()))
						<form action="/auth/logout" method="POST" style="display:inline-block;margin-left:12px">@csrf
							<button type="submit" class="btn-euph secondary" style="border:1px solid rgba(255,255,255,0.06);">Logout</button>
						</form>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
