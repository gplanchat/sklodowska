<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

use ModelflowAi\Core\AIRequestHandler;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTree;
use ModelflowAi\Core\DecisionTree\DecisionRule;
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Core\Request\Criteria\FeatureCriteria;
use ModelflowAi\Core\Request\Criteria\PrivacyCriteria;
use ModelflowAi\Mistral\Mistral;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Model\MistralChatModelAdapter;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Model\OllamaChatModelAdapter;
use ModelflowAi\OllamaAdapter\Model\OllamaCompletionModelAdapter;
use ModelflowAi\OpenaiAdapter\Model\OpenaiChatModelAdapter;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$adapter = [];

$mistralApiKey = $_ENV['MISTRAL_API_KEY'] ?? null;
if ($mistralApiKey) {
    $mistralClient = Mistral::client($mistralApiKey);

    $adapter[] = new DecisionRule(new MistralChatModelAdapter($mistralClient, Model::LARGE), [CapabilityCriteria::SMART, ProviderCriteria::MISTRAL, PrivacyCriteria::MEDIUM, FeatureCriteria::TOOLS]);
    $adapter[] = new DecisionRule(new MistralChatModelAdapter($mistralClient, Model::MEDIUM), [CapabilityCriteria::ADVANCED, ProviderCriteria::MISTRAL, PrivacyCriteria::MEDIUM]);
    $adapter[] = new DecisionRule(new MistralChatModelAdapter($mistralClient, Model::SMALL), [CapabilityCriteria::INTERMEDIATE, ProviderCriteria::MISTRAL, PrivacyCriteria::MEDIUM]);
    $adapter[] = new DecisionRule(new MistralChatModelAdapter($mistralClient, Model::TINY), [CapabilityCriteria::BASIC, ProviderCriteria::MISTRAL, PrivacyCriteria::MEDIUM]);
}

$openaiApiKey = $_ENV['OPENAI_API_KEY'] ?? null;
if ($openaiApiKey) {
    $openAiClient = \OpenAI::client($openaiApiKey);

    $adapter[] = new DecisionRule(new OpenaiChatModelAdapter($openAiClient, 'gpt-4'), [ProviderCriteria::OPENAI, PrivacyCriteria::LOW, CapabilityCriteria::SMART]);
    $adapter[] = new DecisionRule(new OpenaiChatModelAdapter($openAiClient, 'gpt-3.5-turbo-0125'), [ProviderCriteria::OPENAI, PrivacyCriteria::LOW, CapabilityCriteria::ADVANCED]);
}

$client = Ollama::client();
$adapter[] = new DecisionRule(new OllamaCompletionModelAdapter($client), [ProviderCriteria::OLLAMA, PrivacyCriteria::HIGH]);
$adapter[] = new DecisionRule(new OllamaChatModelAdapter($client), [ProviderCriteria::OLLAMA, PrivacyCriteria::HIGH, FeatureCriteria::TOOLS]);

$decisionTree = new AIModelDecisionTree($adapter);

return new AIRequestHandler($decisionTree);
