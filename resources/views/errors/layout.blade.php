<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('code') — @yield('title')</title>
  <link rel="shortcut icon" href="/assets/admin/images/favicon.ico">
  <script src="/assets/admin/js/layout.js"></script>
  <link href="/assets/admin/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/admin/css/icons.min.css" rel="stylesheet">
  <link href="/assets/admin/css/app.min.css" rel="stylesheet">
  <style>
    html, body { height: 100%; }

    .error-page {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      background: linear-gradient(135deg, #f0f4ff 0%, #fafafa 60%, #f5f0ff 100%);
    }

    .error-card {
      background: #fff;
      border-radius: 1.25rem;
      box-shadow: 0 8px 40px rgba(64,81,137,.10);
      padding: 3rem 2.5rem 2.5rem;
      max-width: 520px;
      width: 100%;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .error-bg-code {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -58%);
      font-size: 11rem;
      font-weight: 900;
      line-height: 1;
      color: rgba(64,81,137,.06);
      pointer-events: none;
      user-select: none;
      letter-spacing: -.02em;
      white-space: nowrap;
    }

    .error-icon-wrap {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 2.25rem;
      margin-bottom: 1.25rem;
      position: relative;
      z-index: 1;
    }

    .error-code {
      font-size: 4rem;
      font-weight: 800;
      line-height: 1;
      letter-spacing: -.03em;
      margin-bottom: .25rem;
      position: relative;
      z-index: 1;
    }

    .error-title {
      font-size: 1.35rem;
      font-weight: 700;
      margin-bottom: .5rem;
      position: relative;
      z-index: 1;
    }

    .error-message {
      color: #6c757d;
      font-size: .95rem;
      line-height: 1.6;
      margin-bottom: 2rem;
      position: relative;
      z-index: 1;
    }

    .error-actions {
      display: flex;
      gap: .75rem;
      justify-content: center;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }

    .error-actions .btn {
      min-width: 140px;
      padding: .55rem 1.25rem;
      font-weight: 500;
    }

    .error-divider {
      border-top: 1px solid #f0f0f0;
      margin: 2rem 0 1.5rem;
      position: relative;
      z-index: 1;
    }

    .error-footer {
      font-size: .8rem;
      color: #adb5bd;
      margin-top: 2rem;
    }

    @media (max-width: 480px) {
      .error-card { padding: 2rem 1.25rem 1.75rem; }
      .error-code  { font-size: 3rem; }
      .error-bg-code { font-size: 7rem; }
      .error-actions .btn { min-width: 0; flex: 1; }
    }
  </style>
</head>
<body>

  <div class="error-page">
    <div class="error-card">

      {{-- Watermark code --}}
      <div class="error-bg-code">@yield('code')</div>

      {{-- Icon --}}
      <div class="error-icon-wrap @yield('icon_bg')">
        <i class="@yield('icon')"></i>
      </div>

      {{-- Code + Title --}}
      <div class="error-code @yield('code_color')">@yield('code')</div>
      <div class="error-title">@yield('title')</div>
      <p class="error-message">@yield('message')</p>

      {{-- Actions --}}
      <div class="error-actions">
        @yield('actions')
      </div>

      @hasSection('secondary_actions')
        <div class="error-divider"></div>
        <div class="error-actions">
          @yield('secondary_actions')
        </div>
      @endif

    </div>

    <div class="error-footer">
      &copy; {{ date('Y') }} G2G Automation
    </div>
  </div>

</body>
</html>
