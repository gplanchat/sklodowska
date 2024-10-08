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

use ModelflowAi\Chat\AIChatRequestHandler;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\Criteria\FeatureCriteria;
use ModelflowAi\DecisionTree\Criteria\PrivacyCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\Mistral\Mistral;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Chat\MistralChatAdapterFactory;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Chat\OllamaChatAdapterFactory;
use ModelflowAi\OllamaAdapter\Completion\OllamaCompletionAdapterFactory;
use ModelflowAi\OpenaiAdapter\Chat\OpenaiChatAdapterFactory;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$adapter = [];

$mistralApiKey = $_ENV['MISTRAL_API_KEY'] ?? null;
if ($mistralApiKey) {
    $factory = new MistralChatAdapterFactory(Mistral::client($mistralApiKey));

    $adapter[] = new DecisionRule($factory->createChatAdapter(['model' => Model::LARGE->value]), [CapabilityCriteria::SMART, ProviderCriteria::MISTRAL, PrivacyCriteria::MEDIUM, FeatureCriteria::TOOLS]);
    $adapter[] = new DecisionRule($factory->createChatAdapter(['model' => Model::MEDIUM->value]), [CapabilityCriteria::ADVANCED, ProviderCriteria::MISTRAL, PrivacyCriteria::MEDIUM]);
    $adapter[] = new DecisionRule($factory->createChatAdapter(['model' => Model::SMALL->value]), [CapabilityCriteria::INTERMEDIATE, ProviderCriteria::MISTRAL, PrivacyCriteria::MEDIUM]);
    $adapter[] = new DecisionRule($factory->createChatAdapter(['model' => Model::TINY->value]), [CapabilityCriteria::BASIC, ProviderCriteria::MISTRAL, PrivacyCriteria::MEDIUM]);
}

$openaiApiKey = $_ENV['OPENAI_API_KEY'] ?? null;
if ($openaiApiKey) {
    $factory = new OpenaiChatAdapterFactory(\Openai::client($openaiApiKey));

    $adapter[] = new DecisionRule($factory->createChatAdapter(['model' => 'gpt-4']), [ProviderCriteria::OPENAI, PrivacyCriteria::LOW, CapabilityCriteria::SMART]);
    $adapter[] = new DecisionRule($factory->createChatAdapter(['model' => 'gpt-3.5-turbo-0125']), [ProviderCriteria::OPENAI, PrivacyCriteria::LOW, CapabilityCriteria::ADVANCED]);
}

$adapter[] = new DecisionRule((new OllamaChatAdapterFactory(Ollama::client()))->createChatAdapter(['model' => 'llama2']), [ProviderCriteria::OLLAMA, CapabilityCriteria::ADVANCED, PrivacyCriteria::HIGH, FeatureCriteria::TOOLS]);
$adapter[] = new DecisionRule((new OllamaChatAdapterFactory(Ollama::client()))->createChatAdapter(['model' => 'mixtral:8x7b']), [ProviderCriteria::OLLAMA, CapabilityCriteria::SMART, PrivacyCriteria::HIGH, FeatureCriteria::TOOLS]);
$adapter[] = new DecisionRule((new OllamaChatAdapterFactory(Ollama::client()))->createChatAdapter(['model' => 'mixtral:8x22b']), [ProviderCriteria::OLLAMA, CapabilityCriteria::SMART, PrivacyCriteria::HIGH, FeatureCriteria::TOOLS]);

$decisionTree = new DecisionTree($adapter);

return new AIChatRequestHandler($decisionTree);
