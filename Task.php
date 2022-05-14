<?php
    class Task {
        // Properties
        private $category;
        private $title;
        private $content;
        private $due_date;
        private $priority; //1 = low, 2 = Medium, 3 = High
        private $priorityList = array("Low", "Medium", "High");
        private $completed;
    
        public function __construct(string $category, string $title, 
                                    string $content, string $due_date, 
                                    int $priority, bool $completed = false) {
            $this->category = $category;
            $this->title = $title;
            $this->content = $content;
            $this->due_date = $due_date;
            $this->priority = $priority;
            $this->completed = $completed;
        }
        
        public function getCategory()
        {
            return $this->category;
        }
        public function getTitle()
        {
            return $this->title;
        }
        public function getContent()
        {
            return $this->content;
        }
        public function getDue_date()
        {
            return $this->due_date;
        }
        public function getPriority()
        {
            return $this->priority;
        }
        public function getPriorityAsString()
        {
            return $this->priorityList[ $this->priority - 1 ];
        }
        public function getCompleted()
        {
            return $this->completed;
        }
        public function getCompletedString()
        {
            return ($this->completed)? "true": "false";
        }

        public function printTask()
        {
            echo <<< _END
            Task:<br/>
            Category: $this->category<br/>
            Title:    $this->title<br/>
            Content:  $this->content<br/>
            Due Date: $this->due_date<br/>
            Priority: $this->priority<br/>
            _END;
            echo "PriorityAsString: ".$this->priorityList[$this->priority - 1]."<br/>";
            echo "Completed: ";
            echo ($this->completed)? "true": "false"."<br/>";
        }       

    }

?>