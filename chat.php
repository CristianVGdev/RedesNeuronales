<?php

class NeuralNetwork
{
    private $layers; // Capas de la red neuronal
    private $learningRate; // Tasa de aprendizaje

    public function __construct($layers, $learningRate = 0.1)
    {
        $this->layers = $layers;
        $this->learningRate = $learningRate;
        $this->initializeWeights();
    }

    private function initializeWeights()
    {
        for ($i = 1; $i < count($this->layers); $i++) {
            $numNeuronsCurrentLayer = $this->layers[$i];
            $numNeuronsPrevLayer = $this->layers[$i - 1];
            $weights = [];

            for ($j = 0; $j < $numNeuronsCurrentLayer; $j++) {
                $weights[$j] = [];

                for ($k = 0; $k < $numNeuronsPrevLayer; $k++) {
                    $weights[$j][$k] = $this->getRandomWeight();
                }
            }

            $this->layers[$i] = $weights;
        }
    }

    private function getRandomWeight()
    {
        return (mt_rand() / mt_getrandmax()) * 2 - 1; // Genera un número aleatorio entre -1 y 1
    }

    public function train($input, $target)
    {
        $activations = $this->feedForward($input);
        $gradients = $this->calculateGradients($activations, $target);
        $this->updateWeights($activations, $gradients);
    }

    private function feedForward($input)
    {
        $activations = [$input];

        for ($i = 1; $i < count($this->layers); $i++) {
            $weights = $this->layers[$i];
            $prevActivations = $activations[$i - 1];
            $currentActivations = [];

            for ($j = 0; $j < count($weights); $j++) {
                $sum = 0;

                for ($k = 0; $k < count($weights[$j]); $k++) {
                    $sum += $weights[$j][$k] * $prevActivations[$k];
                }

                $currentActivations[$j] = $this->sigmoid($sum);
            }

            $activations[$i] = $currentActivations;
        }

        return $activations;
    }

    private function calculateGradients($activations, $target)
    {
        $gradients = [];
        $outputLayerIndex = count($this->layers) - 1;

        for ($i = $outputLayerIndex; $i >= 1; $i--) {
            $weights = $this->layers[$i];
            $currentGradients = [];

            if ($i === $outputLayerIndex) {
                for ($j = 0; $j < count($weights); $j++) {
                    $output = $activations[$i][$j];
                    $error = $target[$j] - $output;
                    $gradient = $error * $this->sigmoidDerivative($output);
                    $currentGradients[$j] = $gradient;
                }
            } else {
                $nextGradients = $gradients[$i + 1];

                for ($j = 0; $j < count($weights[0]); $j++) {
                    $sum = 0;

                    for ($k = 0; $k < count($nextGradients); $k++) {
                        $sum += $weights[$k][$j] * $nextGradients[$k];
                    }

                    $gradient = $sum * $this->sigmoidDerivative($activations[$i][$j]);
                    $currentGradients[$j] = $gradient;
                }
            }

            $gradients[$i] = $currentGradients;
        }

        return $gradients;
    }

    private function updateWeights($activations, $gradients)
    {
        for ($i = 1; $i < count($this->layers); $i++) {
            $weights = $this->layers[$i];
            $prevActivations = $activations[$i - 1];

            for ($j = 0; $j < count($weights); $j++) {
                for ($k = 0; $k < count($weights[$j]); $k++) {
                    $weights[$j][$k] += $this->learningRate * $gradients[$i][$j] * $prevActivations[$k];
                }
            }

            $this->layers[$i] = $weights;
        }
    }

    private function sigmoid($x)
    {
        return 1 / (1 + exp(-$x));
    }

    private function sigmoidDerivative($x)
    {
        $sigmoid = $this->sigmoid($x);
        return $sigmoid * (1 - $sigmoid);
    }

    public function predict($input)
    {
        $activations = $this->feedForward($input);
        $outputLayerIndex = count($this->layers) - 1;
        return $activations[$outputLayerIndex];
    }
}

// Clase para la generación de respuestas automáticas
class ChatBot
{
    private $neuralNetwork;
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
        $this->initializeNeuralNetwork();
    }

    private function initializeNeuralNetwork()
    {
        // Obtener los datos de entrenamiento desde la base de datos
        $trainingData = $this->database->getTrainingData();

        // Obtener la estructura de la red neuronal desde la base de datos
        $layers = $this->database->getNeuralNetworkStructure();

        // Crear una instancia de la red neuronal
        $this->neuralNetwork = new NeuralNetwork($layers);

        // Entrenar la red neuronal
        foreach ($trainingData as $data) {
            $input = $data['input'];
            $target = $data['target'];
            $this->neuralNetwork->train($input, $target);
        }
    }

    public function generateResponse($input)
    {
        // Realizar la predicción utilizando la red neuronal
        $output = $this->neuralNetwork->predict($input);

        // Obtener la respuesta correspondiente desde la base de datos
        $response = $this->database->getResponse($output);

        return $response;
    }
}

// Ejemplo de uso

// Clase para la base de datos
class Database
{
    public function getTrainingData()
    {
        // Obtener los datos de entrenamiento desde la base de datos
        return [
            [
                'input' => [0, 0, 1],
                'target' => [0]
            ],
            [
                'input' => [1, 1, 1],
                'target' => [1]
            ],
            [
                'input' => [1, 0, 1],
                'target' => [1]
            ],
            [
                'input' => [0, 1, 1],
                'target' => [0]
            ]
        ];
    }

    public function getNeuralNetworkStructure()
    {
        // Obtener la estructura de la red neuronal desde la base de datos
        return [3, 2, 1];
    }

    public function getResponse($output)
    {
        // Obtener la respuesta correspondiente desde la base de datos
        $response = ''; // Realizar la consulta a la base de datos
        return $response;
    }
}

// Crear una instancia de la base de datos
$database = new Database();

// Crear una instancia del ChatBot
$chatBot = new ChatBot($database);

// Realizar una consulta al ChatBot
$input = [1, 0, 0];
$response = $chatBot->generateResponse($input);

echo "Respuesta: " . $response . "\n";

?>
