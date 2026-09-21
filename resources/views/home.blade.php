@extends('layouts.app')

@section('body-class', 'landing-page')

@push('head')
    <meta name="description" content="Build your coding habit with focused lessons, hands-on Python and JavaScript exercises, and progress you can see.">
    <link rel="stylesheet" href="{{ asset('landing.css') }}?v={{ filemtime(public_path('landing.css')) }}">
    <script defer src="{{ asset('landing.js') }}?v={{ filemtime(public_path('landing.js')) }}"></script>
@endpush

@section('content')
    <div class="landing">
        <svg class="landing-icons" aria-hidden="true" width="0" height="0"><defs>
            <symbol id="landing-arrow" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></symbol>
            <symbol id="landing-down" viewBox="0 0 24 24"><path d="M12 5v14m-6-6 6 6 6-6"/></symbol>
        </defs></svg>
        <div class="landing-backdrop" aria-hidden="true">
            <span class="backdrop-glow backdrop-glow-blue"></span>
            <span class="backdrop-glow backdrop-glow-violet"></span>
            <span class="backdrop-code backdrop-code-top"><i>01</i><code>const momentum = practice();</code></span>
            <span class="backdrop-code backdrop-code-middle"><i>02</i><code>test(); learn(); repeat();</code></span>
            <span class="backdrop-code backdrop-code-bottom"><i>03</i><code>return progress;</code></span>
            <span class="backdrop-bracket backdrop-bracket-left">{</span>
            <span class="backdrop-bracket backdrop-bracket-right">}</span>
        </div>
        <section class="landing-hero" aria-labelledby="landing-title">
            <div class="landing-copy">
                <h1 id="landing-title">Small steps.<br><span>Stronger code.</span></h1>
                <p class="landing-intro">Turn “I think I get it” into “I built that.” Learn a concept, work through a problem, and make a little progress every time you show up.</p>
                <div class="landing-actions">
                    @auth
                        <a class="button landing-primary" href="{{ route('dashboard') }}">Keep practicing <svg aria-hidden="true"><use href="#landing-arrow"/></svg></a>
                    @else
                        <a class="button landing-primary" href="{{ route('register') }}">Start practicing <svg aria-hidden="true"><use href="#landing-arrow"/></svg></a>
                    @endauth
                    <a class="landing-text-link" href="#how-it-works">See how it works <svg aria-hidden="true"><use href="#landing-down"/></svg></a>
                </div>
                <p class="landing-note"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m8 7-5 5 5 5m8-10 5 5-5 5m-3-13-2 16"/></svg> Python &amp; JavaScript. Right in your browser.</p>
                <ul class="landing-flow" aria-label="Learning flow">
                    <li><span>Learn</span><small>Build the idea</small></li>
                    <li><span>Practice</span><small>Test your thinking</small></li>
                    <li><span>Progress</span><small>Keep the momentum</small></li>
                </ul>
            </div>

            <section class="landing-lab" data-landing-lab aria-labelledby="lab-title">
                <div class="lab-topline"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 7 5 5-5 5m8 0h6"/></svg><span>A small function. A good place to start.</span></div>
                <div class="lab-heading">
                    <h2 id="lab-title">Make something add up.</h2>
                    <div class="lab-languages" role="group" aria-label="Example language">
                        <button type="button" data-lab-language="python" aria-pressed="true">Python</button>
                        <button type="button" data-lab-language="javascript" aria-pressed="false">JavaScript</button>
                    </div>
                </div>
                <pre class="lab-code" data-lab-code="python" aria-label="Python addition function"><code><span class="lab-line"><span class="lab-line-number" aria-hidden="true">1</span><span class="lab-comment"># Big ideas start with small functions.</span></span>
<span class="lab-line"><span class="lab-line-number" aria-hidden="true">2</span></span>
<span class="lab-line"><span class="lab-line-number" aria-hidden="true">3</span><span class="lab-keyword">def</span> <span class="lab-function">add</span>(a, b):</span>
<span class="lab-line"><span class="lab-line-number" aria-hidden="true">4</span>    <span class="lab-keyword">return</span> a + b</span>
<span class="lab-line"><span class="lab-line-number" aria-hidden="true">5</span></span></code></pre>
                <pre class="lab-code" data-lab-code="javascript" aria-label="JavaScript addition function" hidden><code><span class="lab-line"><span class="lab-line-number" aria-hidden="true">1</span><span class="lab-comment">// Big ideas start with small functions.</span></span>
<span class="lab-line"><span class="lab-line-number" aria-hidden="true">2</span></span>
<span class="lab-line"><span class="lab-line-number" aria-hidden="true">3</span><span class="lab-keyword">function</span> <span class="lab-function">add</span>(a, b) {</span>
<span class="lab-line"><span class="lab-line-number" aria-hidden="true">4</span>    <span class="lab-keyword">return</span> a + b;</span>
<span class="lab-line"><span class="lab-line-number" aria-hidden="true">5</span>}</span></code></pre>
                <p class="lab-fallback">Two inputs. One function. A new skill to build on.</p>
                <div class="lab-experiment">
                    <div class="lab-inputs">
                        <p>Pick the inputs</p>
                        <div class="lab-presets" role="group" aria-label="Function inputs">
                            <button type="button" data-lab-preset data-a="2" data-b="3" aria-pressed="true">2 + 3</button>
                            <button type="button" data-lab-preset data-a="8" data-b="5" aria-pressed="false">8 + 5</button>
                            <button type="button" data-lab-preset data-a="-4" data-b="4" aria-pressed="false">−4 + 4</button>
                        </div>
                    </div>
                    <div class="lab-result">
                        <div class="lab-output"><code>add(<span data-lab-a>2</span>, <span data-lab-b>3</span>)</code><svg class="lab-result-arrow" aria-hidden="true"><use href="#landing-arrow"/></svg><strong data-lab-output aria-label="Function output">—</strong><svg class="lab-result-mark" aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg></div>
                        <button class="lab-run" type="button" data-lab-run hidden><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m8 5 11 7-11 7Z"/></svg><span data-lab-run-label>Run function</span></button>
                    </div>
                    <p class="lab-status" data-lab-status role="status">Choose an example, then run the function.</p>
                </div>
            </section>
        </section>

        <section class="landing-path" id="how-it-works" aria-labelledby="path-title">
            <div class="landing-path-intro" data-reveal>
                <span class="landing-kicker">From idea to solution</span>
                <h2 id="path-title">Watch an idea become code.</h2>
                <p>Each lesson adds one useful move.</p>
                @auth
                    <a class="landing-text-link" href="{{ route('courses.index') }}">Explore your courses <svg aria-hidden="true"><use href="#landing-arrow"/></svg></a>
                @else
                    <a class="landing-text-link" href="{{ route('register') }}">Start your first course <svg aria-hidden="true"><use href="#landing-arrow"/></svg></a>
                @endauth
            </div>
            <div class="code-story" data-code-story data-story-state="function">
                <div class="code-story-stage" data-reveal>
                    <div class="code-story-bar"><span></span><span></span><span></span><code>practice.py</code><small data-story-label>Function</small></div>
                    <div class="code-story-panels">
                        <pre data-story-panel="values" aria-hidden="true"><code><span><b class="token-variable">score</b> = <b class="token-number">0</b></span><span><b class="token-variable">answer</b> = <b class="token-string">&quot;blue&quot;</b></span></code></pre>
                        <pre data-story-panel="decision" aria-hidden="true"><code><span><b class="token-variable">score</b> = <b class="token-number">0</b></span><span><b class="token-variable">answer</b> = <b class="token-string">&quot;blue&quot;</b></span><span> </span><span><b class="token-keyword">if</b> answer == <b class="token-string">&quot;blue&quot;</b>:</span><span>    score += <b class="token-number">1</b></span></code></pre>
                        <pre data-story-panel="function" aria-hidden="false"><code><span><b class="token-keyword">def</b> <b class="token-function">check</b>(answer):</span><span>    score = <b class="token-number">0</b></span><span>    <b class="token-keyword">if</b> answer == <b class="token-string">&quot;blue&quot;</b>:</span><span>        score += <b class="token-number">1</b></span><span>    <b class="token-keyword">return</b> score</span></code></pre>
                    </div>
                    <div class="code-story-progress" aria-hidden="true"><span></span></div>
                </div>
                <div class="code-story-beats" aria-label="How the example grows">
                    <article data-story-step="values" data-story-label="Values"><span>Values</span><h3>Name it.</h3></article>
                    <article data-story-step="decision" data-story-label="Decision"><span>Condition</span><h3>Check it.</h3></article>
                    <article data-story-step="function" data-story-label="Function" aria-current="step"><span>Function</span><h3>Reuse it.</h3></article>
                </div>
            </div>
        </section>

        <section class="landing-feedback" aria-labelledby="feedback-title">
            <div class="landing-feedback-heading" data-reveal>
                <span class="landing-kicker">Practice with context</span>
                <h2 id="feedback-title">Feedback where you need it.</h2>
                <p>Stay close to the code while you work. See what passed, understand what needs attention, and keep the next useful action in view.</p>
            </div>
            <div class="feedback-layout">
                <div class="feedback-editor" data-reveal>
                    <div class="feedback-editor-bar"><span></span><span></span><span></span><code>challenge.py</code><small>Python</small></div>
                    <div class="feedback-editor-body">
                        <div class="feedback-prompt"><span>Challenge</span><strong>Return the larger number.</strong></div>
                        <pre aria-label="Example Python solution"><code><span><i>1</i><b>def</b> larger(a, b):</span>
<span><i>2</i>    <b>if</b> a &gt; b:</span>
<span class="feedback-active-line"><i>3</i>        <b>return</b> a</span>
<span><i>4</i>    <b>return</b> b</span></code></pre>
                        <div class="feedback-tests"><div><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg><span>larger(8, 3)</span><strong>8</strong></div><div><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg><span>larger(2, 7)</span><strong>7</strong></div><p>2 tests passed <span>Ready to submit</span></p></div>
                    </div>
                </div>
                <div class="feedback-cards">
                    <article data-reveal style="--reveal-delay: 60ms"><div class="feedback-card-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M9 18h6m-5 3h4M8.5 14.5A7 7 0 1 1 16 14c-.7.6-1 1.2-1 2H9c0-.6-.2-1-.5-1.5Z"/></svg></div><h3>A useful hint, not the answer.</h3><p>Get a small nudge that helps you keep thinking and finish the solution yourself.</p><code>Try comparing a and b first.</code></article>
                    <article data-reveal style="--reveal-delay: 120ms"><div class="feedback-card-icon"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 19V9m7 10V5m7 14v-7"/><path d="M3 19h18"/></svg></div><h3>Progress you can see.</h3><p>Completed lessons and solved problems add up into a clear picture of your practice.</p><div class="feedback-progress"><span><i></i></span><small>Course progress</small><strong>68%</strong></div></article>
                </div>
            </div>
        </section>

        <section class="landing-habit" aria-labelledby="habit-title" data-reveal>
            <div class="landing-habit-symbol" aria-hidden="true"><svg viewBox="0 0 64 64"><path d="M13 47V34m13 13V25m13 22V16m13 31V7"/><path d="m8 20 13-7 11 4L48 5"/></svg></div>
            <div class="landing-habit-copy"><h2 id="habit-title">Small wins are worth coming back for.</h2><p>Keep your streak going, see your practice add up, and find your next challenge. A little consistency goes a long way.</p></div>
            @auth
                <a class="landing-text-link" href="{{ route('progress.show') }}">See your progress <svg aria-hidden="true"><use href="#landing-arrow"/></svg></a>
            @else
                <a class="landing-text-link" href="{{ route('register') }}">Build your coding habit <svg aria-hidden="true"><use href="#landing-arrow"/></svg></a>
            @endauth
        </section>

        <section class="landing-demo" id="demo-access" aria-labelledby="demo-title">
            <div class="landing-demo-copy" data-reveal>
                <h2 id="demo-title">Take Coddy for a test run.</h2>
                <p>No setup or new account needed. Use one of the seeded accounts to explore the learner experience or the content tools, then try the full workflow.</p>
                <a class="button landing-primary" href="{{ route('login') }}">Open demo sign in <svg aria-hidden="true"><use href="#landing-arrow"/></svg></a>
            </div>
            <div class="demo-terminal" aria-label="Demo account credentials" data-reveal style="--reveal-delay: 80ms">
                <div class="demo-terminal-bar"><span></span><span></span><span></span><code>demo-access.txt</code></div>
                <div class="demo-account-row">
                    <div><span class="demo-role">Learner</span><strong>Practice courses and problems</strong></div>
                    <dl><div><dt>Email</dt><dd><code>user@example.com</code></dd></div><div><dt>Password</dt><dd><code>password</code></dd></div></dl>
                    <button type="button" class="demo-copy" data-demo-email="user@example.com" data-demo-password="password" aria-label="Copy learner demo credentials"><span>Copy credentials</span><svg aria-hidden="true" viewBox="0 0 24 24"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg></button>
                    <p class="demo-copy-status" role="status" aria-live="polite"></p>
                </div>
                <div class="demo-account-row">
                    <div><span class="demo-role">Admin</span><strong>Explore content management</strong></div>
                    <dl><div><dt>Email</dt><dd><code>admin@example.com</code></dd></div><div><dt>Password</dt><dd><code>password</code></dd></div></dl>
                    <button type="button" class="demo-copy" data-demo-email="admin@example.com" data-demo-password="password" aria-label="Copy admin demo credentials"><span>Copy credentials</span><svg aria-hidden="true" viewBox="0 0 24 24"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg></button>
                    <p class="demo-copy-status" role="status" aria-live="polite"></p>
                </div>
            </div>
        </section>

        <footer class="landing-footer"><a class="brand" href="{{ route('home') }}">Coddy<span class="landing-footer-note">Practice with purpose.</span></a><a href="#landing-title">Back to top <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 19V5m-6 6 6-6 6 6"/></svg></a></footer>
    </div>
@endsection
