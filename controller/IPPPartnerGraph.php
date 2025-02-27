<?php
class IPPPartnerGraph {
    private $user_id;
    private $session_id;
    private $request;
    private $partner;
    public $data_sources = [];

    function __construct($partner, $request,$id = "",$session_id = "") {
        $this->request = $request;

        if($id != "")
            $this->user_id = $id;
        if($session_id != "")
            $this->session_id = $session_id;
        $this->partner = $partner;

        $this->data_sources = $this->getDataSources();
    }

    private function getDataSources() {
        $sources = [];
        $sources["customers_created_7_days"] = [
            "id"            => "customers_created_7_days",
            "title"         => "Created Customers, past 7 days",
            "source"        => "api",
            "datasource"    => "company",
            "serve"         => "list",
            "period"        => 7
        ];
        $sources["customers_created_30_days"] = [
            "id"            => "customers_created_30_days",
            "title"         => "Created Customers, past 30 days",
            "source"        => "api",
            "datasource"    => "company",
            "serve"         => "list",
            "period"        => 30
        ];
        $sources["transactions_approved_7_days"] = [
            "id"            => "transactions_approved_7_days",
            "title"         => "Approved Transactions, past 7 days",
            "source"        => "api",
            "datasource"    => "transactions",
            "serve"         => "list",
            "period"        => 7
        ];
        $sources["transactions_approved_14_days"] = [
            "id"            => "transactions_approved_14_days",
            "title"         => "Approved Transactions, past 14 days",
            "source"        => "api",
            "datasource"    => "transactions",
            "serve"         => "list",
            "period"        => 14
        ];
        $sources["transactions_approved_30_days"] = [
            "id"            => "transactions_approved_30_days",
            "title"         => "Approved Transactions, past 30 days",
            "source"        => "api",
            "datasource"    => "transactions",
            "serve"         => "list",
            "period"        => 30
        ];
        return $sources;
    }
    
    private function StatisticsRequest($data_table, $group_method,$summarize,$period) {
        global $request;
        $dataset    = [];
        $dataset["x"] = "";
        $dataset["y"] = "";
        $data = [
            "since" => (time()-(86400*$period)),
            "table" => $data_table,
            "serve" => "list",
            "group" => "start_time,$group_method"
        ];
        $r_data = $request->curl($_ENV["GLOBAL_BASE_URL"]."/partner/statistics/", "POST", [], $data)->content;
        foreach($r_data->list as $key=>$value) {
            $dataset["x"] .= "'".$key."',";
            $dataset["y"] .= "'".count((array)$value)."',";
        }
        $dataset["x"] = rtrim($dataset["x"],",");
        $dataset["y"] = rtrim($dataset["y"],",");
        return $dataset;
    }
    
    public function GenerateHTML($sequence, $Graph, $Type,$groupBy,$summarize,$live=false) {
        global $inline_script;
        $data = $this->StatisticsRequest($this->data_sources[$Graph]["datasource"],$groupBy,$summarize,$this->data_sources[$Graph]["period"]);
        $script = '
        var '.$this->data_sources[$Graph]["id"].' = echarts.init(document.getElementById("'.$this->data_sources[$Graph]["id"].'"));
        option = {
                xAxis: {
                    data: ['.$data["x"].']
                },
                yAxis: {},
                series: [
                    {
                        type: \''.$Type.'\',
                        data: ['.$data["y"].']
                    }
                ]
            };
            '.$this->data_sources[$Graph]["id"].'.setOption(option);
            console.log("Done");
            ';
        $html = "";
        $html .= "<div class='element' data-sequence='".$sequence."'><h4>".$this->data_sources[$Graph]["title"]."</h4><button class='btn btn-danger DashboardRemoveElement'>Remove</button><div class='graph' id='".$this->data_sources[$Graph]["id"]."'></div></div>";
        if($live)
            echo "<script>".$script."</script>";
        else
            $inline_script[] = $script;
        return $html;
    }
}
