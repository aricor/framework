<?php

class ConfigPreprocessor
{
	private $input; 
	private $tasks;
	public function __construct($config)
	{
		$this->$input = $config;
		$this->$tasks = array();
		

		$this->setUpTasks($config);
		echo "this is the list of all tasks\n";
		var_dump($this->$tasks);
		echo "the tasks are above";
	}

	private function does_it_depend_on($a, $b) {
		echo "this is A\r\n";
		var_dump($a);
		if (in_array($b['id'], $a['dependencies'])) {
			return true; 
		} 
		$is_a_dependencies = false; 
		if (is_array($a["dependencies"]) && count($a["dependencies"]) > 0){
			foreach ($a["dependencies"] as $dep) {
				//get the data for it and call the method to see if we return true 
				$item = null;
				foreach($this->$tasks as $task) {
					if ($dep == $task->id) {
						$this->$tasks  =  $task;
						break;
					}
				}
				$is_a_dependencies = $this->does_it_depend_on($dep, $b) || $is_a_dependencies ;
			}
		}
		return $is_a_dependencies; 
	}
	private function compareTasks($a, $b)
	{
		$temp_a = array();
		$temp_b = array();

		if (is_object($a)) {
			$temp_a['id'] = $a->id;  
			$temp_a['command'] = $a->command;  
			$temp_a['priority'] = $a->priority;  
			$temp_a['dependencies'] = $a->dependencies;  

		} else {
			$temp_a = $a; 
		}
		if (is_object($b)) {
			$temp_b['id'] = $b->id;  
			$temp_b['command'] = $b->command;  
			$temp_b['priority'] = $b->priority;  
			$temp_b['dependencies'] = $b->dependencies;  

		} else {
			$temp_b = $b; 
		}
		//If a task A is on the dependencies list of a task B (i.e., B depends on A), then A must be in the result before B.
		if (in_array($temp_a['id'], $temp_b['dependencies'])) {
//			echo "a is a dependency of b"; 
			return -1; 
		} 

		//A task with a higher priority must appear in the result before a task with lower priority, unless it will violate the previous rule.
		if ($temp_a["priority"] > $temp_b["priority"]) {
	//		echo "a is higher priority than b"; 
			//check if A before B violates rule 1
			// A has higher priority than B, A doesn't go before B because A has the dependencies that B goes in front of if A has a dependencies of C 
			// if C is a depencies of A and B is a dependencies of C then A has to go after C
			if (count($temp_a["dependencies"]) > 0) {
				if ($this->does_it_depend_on($temp_a, $temp_b) ) {
					return 1;
				} else {
					return -1; 
				}
			} else {
				return -1; 
			}
			//if doesn't, return -1
		} 
	 
		if ($temp_a["id"] < $temp_b["id"]) {
			if (!$this->does_it_depend_on($temp_a, $temp_b) && !($temp_b["priority"] > $temp_a["priority"])) {
				return -1; 
			} else {
				return 1; 
			}
		} 
		
	//	if ($temp_a['id'] < $temp_b['id']) {
	//		return -1; 
	//	}  else {
	//		return 1; 
	//	}
		//If a task A is before task B in a prefix serialization of the configuration tree, then A should appear before B in the result unless it will violate the previous two rules.
				 

	}

	private function setUpTasks($input) {
		//echo "setUpTasks";

		//var_dump($input);
		// recursion 
		if (is_object($input) || is_array($input)) {
			foreach ($input as $sub) 
				$this->setUpTasks($sub);
		}

		//base case 
		if ($this->isTask($input)) { 
			array_push($this->$tasks, $input);
		//	echo "it is a task";
		}

	}



	private function isTask($element) {
	//	echo "this task";
	//	var_dump($element);
		// Check the type 
		if (is_array($element)) {
	//		echo "is an array";   
			if (array_key_exists("id", $element) &&
				array_key_exists("command", $element) && 
				array_key_exists("priority", $element) &&
				array_key_exists("dependencies", $element)
			) { 
				return true; 
		//		echo "key exists"; 
			}
			

		}
	
		if (is_object($element)) {
	//		echo "is an object";   
			if (property_exists($element, "id") &&
				property_exists($element, "command") &&
				property_exists($element, "priority") &&
				property_exists($element, "dependencies") 
			) 
			{
				return true; 
	//			echo "property exists";
			}

		}
	
		//Based on the type
		//Check the 4 keys
//		id - nonempty string with unique task identifier
//command - nonempty string holding the command to execute
//priority - an integer representing the importance of the task (higher number means that the task should be executed sooner)
//dependencies - an array of task identifiers (strings) on which the current task depends on (the dependencies must be executed sooner than the depending task)
	
		return false; // it's not a task 
		//If it's not return false
	}

	/**
	 * Get an array of tasks from the config in the right order.
	 */
	public function getAllTasks()
	{
		$sortedTasks = $this->$tasks;

		
		$a = array(3, 2, 5, 6, 1);

		usort($sortedTasks, array($this, "compareTasks"));

		
		//get each task 
		//compare each task criteria





		return $sortedTasks;
	}
}
