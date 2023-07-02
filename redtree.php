<?php

class RandomForest
{
    private $numTrees; // Número de árboles en el bosque
    private $maxDepth; // Profundidad máxima de cada árbol
    private $trees; // Array para almacenar los árboles del bosque

    public function __construct($numTrees = 10, $maxDepth = 5)
    {
        $this->numTrees = $numTrees;
        $this->maxDepth = $maxDepth;
        $this->trees = [];
    }

    public function entrenar($data)
    {
        // Entrenar los árboles del bosque
        for ($i = 0; $i < $this->numTrees; $i++) {
            $tree = $this->construirArbol($data, $this->maxDepth);
            $this->trees[] = $tree;
        }
    }

    public function predecir($input)
    {
        $predictions = [];

        // Realizar predicciones con cada árbol del bosque
        foreach ($this->trees as $tree) {
            $prediction = $this->hacerPrediccion($input, $tree);
            $predictions[] = $prediction;
        }

        // Devolver la predicción más común entre los árboles
        $prediction = $this->obtenerPrediccionMasComun($predictions);
        return $prediction;
    }

    private function construirArbol($data, $maxDepth)
    {
        // Lógica para construir el árbol de decisión
        if ($maxDepth === 0 || $this->todosLosDatosSonIguales($data)) {
            // Crear nodo hoja y asignar la clase más común
            $leafNode = new Node();
            $leafNode->esHoja = true;
            $leafNode->clase = $this->obtenerClaseMasComun($data);
            return $leafNode;
        } else {
            // Seleccionar el mejor atributo para dividir los datos
            $mejorAtributo = $this->seleccionarMejorAtributo($data);
            $nodosHijos = [];

            // Dividir los datos en función del mejor atributo
            foreach ($mejorAtributo->valores as $valor) {
                $subconjuntoData = $this->obtenerSubconjuntoData($data, $mejorAtributo, $valor);
                if (count($subconjuntoData) > 0) {
                    $nodoHijo = new Node();
                    $nodoHijo->atributo = $mejorAtributo;
                    $nodoHijo->valor = $valor;
                    $nodoHijo->subarbol = $this->construirArbol($subconjuntoData, $maxDepth - 1);
                    $nodosHijos[] = $nodoHijo;
                }
            }

            // Crear nodo de decisión y asignar los nodos hijos
            $decisionNode = new Node();
            $decisionNode->esHoja = false;
            $decisionNode->nodosHijos = $nodosHijos;
            return $decisionNode;
        }
    }

    private function hacerPrediccion($input, $tree)
    {
        // Lógica para hacer una predicción basada en el árbol de decisión
        if ($tree->esHoja) {
            return $tree->clase;
        } else {
            foreach ($tree->nodosHijos as $nodoHijo) {
                if ($input[$nodoHijo->atributo->nombre] == $nodoHijo->valor) {
                    return $this->hacerPrediccion($input, $nodoHijo->subarbol);
                }
            }
        }
    }

    private function obtenerPrediccionMasComun($predictions)
    {
        // Lógica para determinar la predicción más común entre los árboles
        $frecuencia = array_count_values($predictions);
        arsort($frecuencia);
        $prediccionMasComun = array_key_first($frecuencia);
        return $prediccionMasComun;
    }

    private function todosLosDatosSonIguales($data)
    {
        // Lógica para verificar si todos los datos tienen la misma clase
        $clasePrimeraInstancia = $data[0]['clase'];
        foreach ($data as $instancia) {
            if ($instancia['clase'] != $clasePrimeraInstancia) {
                return false;
            }
        }
        return true;
    }

    private function obtenerClaseMasComun($data)
    {
        // Lógica para obtener la clase más común en los datos
        $clases = array_column($data, 'clase');
        $frecuencia = array_count_values($clases);
        arsort($frecuencia);
        $claseMasComun = array_key_first($frecuencia);
        return $claseMasComun;
    }

    private function seleccionarMejorAtributo($data)
    {
        // Lógica para seleccionar el mejor atributo para dividir los datos
        $atributos = array_keys($data[0]);
        $atributos = array_diff($atributos, ['clase']);
        $mejorAtributo = null;
        $mejorGanancia = -INF;

        foreach ($atributos as $atributo) {
            $valores = array_unique(array_column($data, $atributo));
            $entropiaInicial = $this->calcularEntropia($data);

            $ganancia = $entropiaInicial;
            foreach ($valores as $valor) {
                $subconjuntoData = $this->obtenerSubconjuntoData($data, $atributo, $valor);
                $fraccion = count($subconjuntoData) / count($data);
                $ganancia -= $fraccion * $this->calcularEntropia($subconjuntoData);
            }

            if ($ganancia > $mejorGanancia) {
                $mejorGanancia = $ganancia;
                $mejorAtributo = new Attribute($atributo, $valores);
            }
        }

        return $mejorAtributo;
    }

    private function obtenerSubconjuntoData($data, $atributo, $valor)
    {
        // Lógica para obtener un subconjunto de datos basado en un atributo y un valor
        $subconjuntoData = array_filter($data, function ($instancia) use ($atributo, $valor) {
            return $instancia[$atributo] == $valor;
        });
        return $subconjuntoData;
    }

    private function calcularEntropia($data)
    {
        // Lógica para calcular la entropía de los datos
        $clases = array_column($data, 'clase');
        $frecuencia = array_count_values($clases);
        $totalInstancias = count($data);

        $entropia = 0;
        foreach ($frecuencia as $clase) {
            $probabilidad = $clase / $totalInstancias;
            $entropia -= $probabilidad * log($probabilidad, 2);
        }

        return $entropia;
    }
}

class Node
{
    public $esHoja; // Indica si el nodo es una hoja o una decisión
    public $clase; // Clase asignada al nodo hoja
    public $atributo; // Atributo utilizado para tomar decisiones
    public $valor; // Valor del atributo para la división
    public $subarbol; // Subárbol para la decisión
    public $nodosHijos; // Nodos hijos para la decisión
}

class Attribute
{
    public $nombre; // Nombre del atributo
    public $valores; // Valores únicos del atributo

    public function __construct($nombre, $valores)
    {
        $this->nombre = $nombre;
        $this->valores = $valores;
    }
}

?>
