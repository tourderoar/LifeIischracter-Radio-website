<?php
/**
 * Life is Character Radio - 7-Day Devotional
 * Location: /devotional.php
 *
 * Funnel page reached at /devotional and linked from the welcome email.
 * Deliberately kept out of the main nav and marked noindex so it reads as
 * a gift for subscribers rather than another menu item.
 */
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Life is a Script: 7-Day Devotional';
$pageDescription = 'A free 7-day devotional from Life is Character Radio. Life is a script. Win with character.';
$pageRobots = 'noindex';
$currentPage = 'devotional';

// The seven days. Scriptures are King James Version.
$days = [
    [
        'title'     => 'The Script',
        'verse'     => 'All scripture is given by inspiration of God, and is profitable for doctrine, for reproof, for correction, for instruction in righteousness.',
        'reference' => '2 Timothy 3:16',
        'body'      => 'Every great actor starts with the script. They read it, study it and let it shape every word and movement on stage. Life works the same way. God has given us a script, His Word, and it was written so we can know who He is and who we are meant to be. Many of us try to improvise our way through life and wonder why the scenes keep going wrong. Today, pick up the script again.',
        'action'    => 'Read one chapter of Proverbs today, the one that matches today\'s date.',
        'prayer'    => 'Lord, teach me to read Your Word as the script for my life. Amen.',
    ],
    [
        'title'     => 'The Author',
        'verse'     => 'Looking unto Jesus the author and finisher of our faith.',
        'reference' => 'Hebrews 12:2',
        'body'      => 'A script has an author, and the author knows how the story ends. When a scene feels confusing or painful, the actor trusts the writer. Jesus is the author and finisher of your faith. He began your story and He will complete it. You don\'t have to write the ending yourself. Your part is to trust the One who already has.',
        'action'    => 'Write down one situation you\'ve been trying to control, and pray over it by name.',
        'prayer'    => 'Jesus, You are the author of my story. I trust You with the ending. Amen.',
    ],
    [
        'title'     => 'Your Role',
        'verse'     => 'For we are his workmanship, created in Christ Jesus unto good works, which God hath before ordained that we should walk in them.',
        'reference' => 'Ephesians 2:10',
        'body'      => 'No role in a production is an accident. The director casts each actor for a reason. God created you on purpose, for a purpose, and prepared good works for you before you were born. Comparing your role to someone else\'s only distracts you from your own. Ask God today what scene He has written for you.',
        'action'    => 'Do one good thing today for someone who can\'t repay you.',
        'prayer'    => 'Father, show me the role You created me to play, and help me play it well. Amen.',
    ],
    [
        'title'     => 'Integrity When No One Is Watching',
        'verse'     => 'He that walketh uprightly walketh surely: but he that perverteth his ways shall be known.',
        'reference' => 'Proverbs 10:9',
        'body'      => 'An actor\'s true skill shows in rehearsal, when the seats are empty. Character works the same way. Who you are when no one is watching is who you really are. Integrity means being the same person in private as in public. It also brings peace: the upright person walks securely, with nothing to hide.',
        'action'    => 'Keep one small promise today that no one would notice if you broke.',
        'prayer'    => 'Lord, make me the same person in private as I am in public. Amen.',
    ],
    [
        'title'     => 'When You Miss Your Line',
        'verse'     => 'For a just man falleth seven times, and riseth up again.',
        'reference' => 'Proverbs 24:16',
        'body'      => 'Every actor misses a line at some point. The professionals don\'t walk off the stage; they recover and keep going. Failure is part of every life, but it is never the end of your story. The righteous person is not the one who never falls. It is the one who gets back up, again and again, by God\'s grace.',
        'action'    => 'Name one recent failure, confess it to God, and take one step to make it right.',
        'prayer'    => 'Lord, when I fall, help me rise again in Your strength. Amen.',
    ],
    [
        'title'     => 'The Scene of Temptation',
        'verse'     => 'God is faithful, who will not suffer you to be tempted above that ye are able; but will with the temptation also make a way to escape.',
        'reference' => '1 Corinthians 10:13',
        'body'      => 'Every story has a scene where the character is tested. Temptation is not a sign that you\'ve failed; it is a moment to show who you are. God promises two things: you won\'t face more than you can bear, and He always writes an exit into the scene. Your job is to look for the door and walk through it.',
        'action'    => 'Identify your most common temptation and decide now what your "way of escape" will be.',
        'prayer'    => 'Faithful God, open my eyes to the way out You have already provided. Amen.',
    ],
    [
        'title'     => 'Trusting the Director',
        'verse'     => 'Trust in the LORD with all thine heart; and lean not unto thine own understanding. In all thy ways acknowledge him, and he shall direct thy paths.',
        'reference' => 'Proverbs 3:5–6',
        'body'      => 'Actors rarely see the whole film while they\'re shooting it. They trust the director to put the scenes together. Uncertainty and loss can make life feel like scattered scenes that don\'t connect. God sees the whole picture. When you trust Him instead of your own understanding, He directs your path, one scene at a time. Life is a script. Win with character.',
        'action'    => 'Choose one area of uncertainty and say out loud, "Lord, I trust You with this."',
        'prayer'    => 'Lord, I trust You as the director of my life. Lead me scene by scene. Amen.',
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .verse { border-left: 3px solid #00E5FF; }
    @media print {
        nav, footer, .no-print { display: none !important; }
        body { background: #fff !important; color: #000 !important; }
        .card { border: 1px solid #ccc !important; background: #fff !important; break-inside: avoid; }
        .card h2, .card p, .card blockquote { color: #000 !important; }
    }
</style>

<!-- Devotional Header -->
<header class="max-w-3xl mx-auto px-4 pt-32 md:pt-40 pb-8 text-center">
    <p class="text-sm font-semibold tracking-widest uppercase text-brand-cyan mb-3">Free 7-day devotional</p>
    <h1 class="text-4xl md:text-5xl font-bold font-cinematic mb-4">Life is a Script</h1>
    <p class="text-xl text-gray-300 mb-2">7 Days to Living with Character</p>
    <p class="text-gray-400">Hosted by Paul Kumeni Agbo. Scriptures from the King James Version.</p>
    <p class="text-gray-400 mt-4">Each day takes about five minutes: one scripture, a short reflection, one action step and a prayer.</p>
    <button onclick="window.print()" class="no-print mt-6 border border-brand-cyan text-brand-cyan px-5 py-2 rounded-lg hover:bg-brand-cyan hover:text-gray-900 transition-colors">
        <i class="fas fa-print mr-2"></i>Print or save as PDF
    </button>
</header>

<!-- The Seven Days -->
<main class="max-w-3xl mx-auto px-4 pb-16 space-y-8">
    <?php foreach ($days as $i => $day): ?>
    <article class="card bg-gray-800 border border-gray-700 rounded-xl p-6 md:p-8">
        <p class="text-brand-cyan font-semibold mb-1">Day <?= $i + 1 ?></p>
        <h2 class="text-2xl font-bold mb-4"><?= htmlspecialchars($day['title']) ?></h2>
        <blockquote class="verse pl-4 italic text-gray-200 mb-4">
            &ldquo;<?= htmlspecialchars($day['verse']) ?>&rdquo;
            <span class="not-italic text-gray-400">(<?= htmlspecialchars($day['reference']) ?>)</span>
        </blockquote>
        <p class="text-gray-300 mb-4"><?= htmlspecialchars($day['body']) ?></p>
        <p class="text-gray-300 mb-2"><strong class="text-white">Character in action:</strong> <?= htmlspecialchars($day['action']) ?></p>
        <p class="text-gray-300"><strong class="text-white">Prayer:</strong> <?= htmlspecialchars($day['prayer']) ?></p>
    </article>
    <?php endforeach; ?>

    <!-- Keep Growing CTA -->
    <section class="no-print text-center bg-gradient-to-br from-gray-800 to-gray-900 border border-brand-cyan/30 rounded-xl p-8">
        <h2 class="text-2xl font-bold mb-3">Keep growing with us</h2>
        <p class="text-gray-300 mb-6">New episodes every week, plus a live show on Mixlr.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="https://open.spotify.com/show/4TGAAkFbSA8U0fCOz3L0Fj" target="_blank" class="border-2 border-spotify text-spotify px-6 py-3 rounded-lg font-semibold hover:bg-spotify hover:text-gray-900 transition-all">
                <i class="fab fa-spotify mr-2"></i>Spotify
            </a>
            <a href="https://www.youtube.com/@lifeischaracterradio959" target="_blank" class="border-2 border-youtube text-youtube px-6 py-3 rounded-lg font-semibold hover:bg-youtube hover:text-white transition-all">
                <i class="fab fa-youtube mr-2"></i>YouTube
            </a>
            <a href="https://life-is-character.mixlr.com/" target="_blank" class="border-2 border-brand-cyan text-brand-cyan px-6 py-3 rounded-lg font-semibold hover:bg-brand-cyan hover:text-gray-900 transition-all">
                <i class="fas fa-broadcast-tower mr-2"></i>Live on Mixlr
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
