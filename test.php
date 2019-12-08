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
		//echo "this is the list of all tasks\n";
		//var_dump($this->$tasks);
		//echo "the tasks are above";
	}
	private function has_higher_priority($a,$b) {
		return $a["priority"] >= $b["priority"] ;
	}
	private function does_it_depend_on($a, $b, $has_switched = false) {
		echo "** does_it_depend_on ** \r\n";
		echo "\r\n ** A ** \r\n";
		echo $a["id"];
		echo "\r\n ** B ** \r\n";
		echo $b["id"];

		echo "\r\n ** compareTasks ** \r\n";
		//echo $this->compareTasks($a,$b);

		if (($this->directly_depends_on($a,$b) || $this->has_strictly_higher_priority($b,$a))) {
			echo "\r\n *Y* A is on dependencies list of B *Y*\r\n";
			if ($has_switched) {
				return true; 
			} else {
				return !$this->does_it_depend_on($b,$a, true);
			}


		} 
		$is_a_dependencies = false; 
		if (is_array($a["dependencies"]) && count($a["dependencies"]) > 0){
			foreach ($a["dependencies"] as $dep) {
				//get the data for it and call the method to see if we return true 
				$item = null;
				foreach($this->$tasks as $task) {
					if ($dep == $task["id"]) {
						$item = $task; 
						echo " \r\n ABOUT TO BREAK \r\n";
						break;
					}
				}
				$is_a_dependencies = $this->does_it_depend_on($item, $b) || $is_a_dependencies ;
			}
		}
		echo "\r\n *X* does_it_depend_on return *X* \r\n";
		echo $is_a_dependencies;
		return $is_a_dependencies; 
	}
	private function directly_depends_on($a,$b) {
		if (in_array($b['id'], $a['dependencies'])) {
			echo "\r\n *Y* A has dependencies list of B *Y*\r\n";
			return true; 
		} 
	}
	private function indexOf($task) {
		foreach($this->$tasks as $key=>$value) {
			if ($task["id"] == $value["id"]) {
				return $key; 
			}
		}
		
	}

	private function has_strictly_higher_priority($a, $b) {
		return $a["priority"] > $b["priority"] ;
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
		echo "\r\n compareTasks \r\n";
		echo "\r\n A \r\n";
		echo $temp_a["id"];
		echo "\r\n B \r\n";
		echo $temp_b["id"];
		//If a task A is on the dependencies list of a task B (i.e., B depends on A), then A must be in the result before B.
		if ($this->directly_depends_on($temp_b,$temp_a)) {
			echo "\r\n a is a DIRECT dependency of b \r\n"; 
			return -1; 
		} 
		if ($this->directly_depends_on($temp_a,$temp_b)) {
			echo "\r\n B is a DIRECT dependency of A \r\n"; 
			return 1; 
		} 
		//A task with a higher priority must appear in the result before a task with lower priority, unless it will violate the previous rule.
		if ($this->has_strictly_higher_priority($temp_a,$temp_b)) {
			echo "\r\na is higher priority than b \r\n"; 
			//check if A before B violates rule 1
			// A has higher priority than B, A doesn't go before B because A has the dependencies that B goes in front of if A has a dependencies of C 
			// if C is a depencies of A and B is a dependencies of C then A has to go after C
		
			if (count($temp_a["dependencies"]) > 0) {
				if ($this->does_it_depend_on($temp_a, $temp_b) ) {
					echo "\r\nBUT it depends on B\r\n";
					return 1;
				} else {
					return -1; 
				}
			} else {
				return -1; 
			}
			//if doesn't, return -1
		} 
	 
		if ($this->has_strictly_higher_priority($temp_b,$temp_a)) {
			echo "\r\nB is stricly higher priority than A \r\n"; 
			//check if A before B violates rule 1
			// A has higher priority than B, A doesn't go before B because A has the dependencies that B goes in front of if A has a dependencies of C 
			// if C is a depencies of A and B is a dependencies of C then A has to go after C
		
			if (count($temp_b["dependencies"]) > 0) {
				if ($this->does_it_depend_on($temp_b, $temp_a) ) {
					echo "\r\nBUT it depends on A\r\n";
					return -1;
				} else {
					return 1; 
				}
			} else {
				return 1; 
			}
			//if doesn't, return -1
		} 
		//find index of temp_a
		echo " \r\n index of A \r\n";
		$index_of_a = $this->indexOf($temp_a);
		echo $index_of_a; 
		// find index of temp_b
		echo " \r\n index of B \r\n";
		$index_of_b = $this->indexOf($temp_b);
		echo $index_of_b; 

		if ($index_of_a < $index_of_b) {
			echo " \r\n A has smaller index than B \r\n";
			if (!$this->does_it_depend_on($temp_a, $temp_b) && 	$this->has_higher_priority($temp_a,$temp_b)) {
			echo " \r\n ALSO  A doesn't depend on B and A doesn't have strictly lower priority than B \r\n";

				return -1; 
			} else {
				echo '\r\n $this->does_it_depend_on($temp_a, $temp_b) \r\n' ; 
				echo $this->does_it_depend_on($temp_a, $temp_b); 
				echo " \r\n BUT A depends on B or has lower priority \r\n";
				return 1; 
			}
		} else {
			echo " \r\n B has lower index than A \r\n";
			return 1; 
		}
		
				 

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
			$task = []; 
			if (is_object($input)) {
				$task['id'] = $input->id;  
				$task['command'] = $input->command;  
				$task['priority'] = $input->priority;  
				$task['dependencies'] = $input->dependencies;  
	
			} else {
				$task = $input; 
			}
			array_push($this->$tasks, $task);
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
