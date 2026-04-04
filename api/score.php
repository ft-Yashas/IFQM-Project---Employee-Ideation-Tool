<?php
require_once __DIR__ . '/config.php';

function computeIdeaScore(array $idea): int
{
    $score = 0;

    $titleWords = str_word_count(trim((string)($idea['title'] ?? '')));
    if      ($titleWords >= 4 && $titleWords <= 15) $score += 8;
    elseif  ($titleWords >= 2)                      $score += 4;
    elseif  ($titleWords === 1)                     $score += 1;

    $sitLen = mb_strlen(trim((string)($idea['present_situation'] ?? '')));
    $score += min(15, (int)floor($sitLen / 20));

    $sitKeywords = [
        'problem','issue','defect','waste','rework','delay','bottleneck',
        'inefficiency','loss','risk','error','failure','inconsistent',
        'manual','excessive','rate','percentage','currently','approximately',
        'causing','results in','high','low',
    ];
    $sitText  = mb_strtolower((string)($idea['present_situation'] ?? ''));
    $kwSitHit = 0;
    foreach ($sitKeywords as $kw) {
        if (str_contains($sitText, $kw)) $kwSitHit++;
    }
    $score += min(10, $kwSitHit * 2);

    $solLen = mb_strlen(trim((string)($idea['proposed_solution'] ?? '')));
    $score += min(15, (int)floor($solLen / 20));

    $solKeywords = [
        'automate','implement','introduce','reduce','improve','standardize',
        'digitize','eliminate','install','train','monitor','check','verify',
        'prevent','optimize','system','process','procedure','mandatory',
        'checklist','digital','gate','review','track','measure',
    ];
    $solText  = mb_strtolower((string)($idea['proposed_solution'] ?? ''));
    $kwSolHit = 0;
    foreach ($solKeywords as $kw) {
        if (str_contains($solText, $kw)) $kwSolHit++;
    }
    $score += min(10, $kwSolHit * 2);

    $impactMap = ['Low' => 7, 'Medium' => 14, 'High' => 20];
    $score += $impactMap[$idea['impact_level'] ?? 'Medium'] ?? 14;

    $impAreas = array_filter(
        array_map('trim', explode(',', (string)($idea['impact_areas'] ?? '')))
    );
    $score += min(10, count($impAreas) * 2);

    $tangible = trim((string)($idea['tangible_benefit'] ?? ''));
    if ($tangible !== '') {
        $score += min(7, (int)ceil(mb_strlen($tangible) / 7));
    }

    if (trim((string)($idea['intangible_benefit'] ?? '')) !== '') {
        $score += 3;
    }

    $coCount = (!empty($idea['co_suggester_1_id']) ? 1 : 0)
             + (!empty($idea['co_suggester_2_id']) ? 1 : 0);
    $score += $coCount;

    return max(0, min(100, $score));
}

function computeAIScoreWithReason(array $idea): array
{
    $title = (string)($idea['title']             ?? '');
    $sit   = (string)($idea['present_situation'] ?? '');
    $sol   = (string)($idea['proposed_solution'] ?? '');
    $areas = (string)($idea['impact_areas']      ?? '');
    $level = (string)($idea['impact_level']      ?? 'Medium');

    $prompt = <<<PROMPT
Evaluate this employee improvement idea for an operations/manufacturing company.

Return ONLY valid JSON in this exact format — no markdown, no code fences, no extra text:
{"score": <integer 0-100>, "reason": "<2-3 sentence explanation>"}

Score based on:
- Innovation: Is it a fresh or creative approach?
- Feasibility: Can it realistically be implemented?
- Business Impact: Does it improve cost, quality, safety, or efficiency?

Idea:
Title: {$title}
Present Situation: {$sit}
Proposed Solution: {$sol}
Impact Areas: {$areas}
Impact Level: {$level}
PROMPT;

    $content = callOpenAI($prompt);

    if ($content !== null) {
        $cleaned = trim($content);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/i',          '', $cleaned);
        $cleaned = trim($cleaned);

        if (preg_match('/\{.*\}/s', $cleaned, $matches)) {
            $parsed = json_decode($matches[0], true);

            if (
                is_array($parsed)
                && array_key_exists('score', $parsed)
                && is_numeric($parsed['score'])
            ) {
                $score  = max(0, min(100, (int)round((float)$parsed['score'])));
                $reason = trim((string)($parsed['reason'] ?? 'Evaluated by AI.'));

                return [
                    'score'  => $score,
                    'reason' => $reason !== '' ? $reason : 'Evaluated by AI.',
                    'source' => 'openai',
                ];
            }
        }

        error_log('OpenAI score parse failed. Cleaned content: ' . $cleaned);
    }

    return [
        'score'  => computeIdeaScore($idea),
        'reason' => 'Scored using the structured rule-based model (AI service unavailable).',
        'source' => 'fallback',
    ];
}

function saveIdeaScore(int $ideaId, int $score, string $reason = ''): void
{
    db()->prepare("UPDATE ideas SET ai_score = ?, ai_reason = ? WHERE id = ?")
       ->execute([$score, $reason, $ideaId]);
}

if (basename($_SERVER['PHP_SELF']) !== 'score.php') return;

$user   = requireAuth();
$action = $_GET['action'] ?? 'score';

if ($action === 'score') {
    $id   = (int)($_GET['id'] ?? 0);
    $stmt = db()->prepare("SELECT * FROM ideas WHERE id = ?");
    $stmt->execute([$id]);
    $idea = $stmt->fetch();
    if (!$idea) respond(['success' => false, 'error' => 'Idea not found.'], 404);

    $ai = computeAIScoreWithReason($idea);
    saveIdeaScore($id, $ai['score'], $ai['reason']);
    respond([
        'success'   => true,
        'id'        => $id,
        'ai_score'  => $ai['score'],
        'ai_reason' => $ai['reason'],
        'source'    => $ai['source'],
    ]);
}

if ($action === 'batch_rescore' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRole('admin');
    $ideas   = db()->query("SELECT * FROM ideas")->fetchAll();
    $updated = 0;
    foreach ($ideas as $idea) {
        $ai = computeAIScoreWithReason($idea);
        saveIdeaScore((int)$idea['id'], $ai['score'], $ai['reason']);
        $updated++;
    }
    respond(['success' => true, 'updated' => $updated]);
}

respond(['success' => false, 'error' => 'Unknown action'], 400);
