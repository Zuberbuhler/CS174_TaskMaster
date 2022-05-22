<?php // Task.php Code by Matthew Zuberbuhler & Karan Sharma
    class Task {
        // Properties
        private $category;
        private $title;
        private $content;
        private $due_date;
        private $status; //1 = Not Started, 2 = In Progress, 3 = Completed
        private $statusList = array("Not Started", "In Progress", "Completed");
        private $completed;
    
        public function __construct(string $category, string $title, 
                                    string $content, string $due_date, 
                                    int $status) {
            $this->category = $category;
            $this->title = $title;
            $this->content = $content;
            $this->due_date = $due_date;
            $this->status = $status;
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
        public function getStatus()
        {
            return $this->status;
        }
        public function getStatusAsString()
        {
            return $this->statusList[ $this->status - 1 ];
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
            Status: $this->status<br/>
            _END;
            echo "StatusAsString: ".$this->statusList[$this->status - 1]."<br/>";
        }       

    }

?>