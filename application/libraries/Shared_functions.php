<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

//use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class shared_functions 
{
	protected $CI;
	
	public function __construct()
    {
        $this->CI =& get_instance();
		
        /* Standard Libraries of codeigniter are required */
        $this->CI->load->database();
        $this->CI->load->helper('url');
		$this->CI->load->helper('form');
        /* ------------------ */ 
		
    }
	

/*	
	public function sf_export_to_XLS($data, $file_name, $column_name=null)
	{
		$spreadsheet = new Spreadsheet(); // instantiate Spreadsheet

		$sheet = $spreadsheet->getActiveSheet();

		// manually set table data value
		//$sheet->setCellValue('A1', 'Cell content'); 
		
		$data_xls = array();
		$fields = array_keys(max($data));

		if($column_name)
			$fields = $column_name;
		else 
			$fields = array_keys(max($data));

		$xls_column = 1;
		foreach($fields as $key => $value)
		{
			$sheet->setCellValueByColumnAndRow($xls_column,1, $fields[$key]);
			//echo $fields[$key];
			$xls_column++;
			
		}

		$xls_row = 2;
		foreach($data as $row)
		{
			$xls_column = 1;
			foreach($row as $key => $value)
			{
				$sheet->setCellValueByColumnAndRow($xls_column,$xls_row, $row[$key]);
				$xls_column++;
			}
			$xls_row++;
		}
		
		$writer = new Xlsx($spreadsheet); // instantiate Xlsx
	 
		header('Content-Type: application/vnd.ms-excel'); // generate excel file
		header('Content-Disposition: attachment;filename="'. $file_name .'"'); 
		header('Cache-Control: max-age=0');
			
		$writer->save('php://output');	// download file 
		
		return;
	}
*/
	
	
/*
	public function sf_import_from_XLS($file_name)
	{
	
		//$file_name = './sampleData/example1.xls';

		//Create a new Xls Reader
		//    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		//    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xml();
		//    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
		//    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Slk();
		//    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Gnumeric();
		//    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
		//Load $inputFileName to a Spreadsheet Object
		$spreadsheet = $reader->load($file_name);
		
		$sheet = $spreadsheet->getActiveSheet();
		$dataArray = $spreadsheet->getActiveSheet()->toArray();
		
print_r($dataArray);
die();
		return;
	
	}
*/
	
/*	
	public function sf_get_configuration($table_name, $field_name, $field_value)
	{
		$configuration_data = $this->CI->db->get_where($table_name,array($field_name => $field_value))->row();
		return $configuration_data;
	}
*/	
	
		
	public function sf_save_log($table_name, $caller = null, $record_type = null, $record_content, $user_upd = null)
	{
		$log_record = array();
		$log_record['caller'] = $caller;
		$log_record['record_type'] = $record_type;
		$log_record['record_content'] = $record_content;
		//$log_record['debug'] = $debug;
		$log_record['user_upd'] = $user_upd;
		$log_record['timestamp_upd'] = date('Y-m-d H:i:s');
		
		$this->CI->db->insert($table_name, $log_record);
		
		return true;
	}

	
	public function sf_get_request_content()
	{
		$request_content = array();
		$request_content = $_SERVER;
		$request_content['raw_input_stream'] = $this->CI->input->raw_input_stream;
		//$request_content['raw_input_stream'] = $raw_input_stream;
		
		return json_encode($request_content,JSON_PRETTY_PRINT);
	}

	
}
