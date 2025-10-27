<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CSVImport Library
 *
 * @author      Stephen Cozart
 * @link        https://github.com/stephencozart
 * @version     1.0.0
 */
class Csvimport {

    private $filepath = '';
    private $column_headers = array();
    private $delimiter = ',';
    private $detect_line_endings = FALSE;
    private $remove_header_row = TRUE;

    public function __construct(array $config = array())
    {
        if (count($config) > 0)
        {
            $this->initialize($config);
        }

        log_message('debug', "CSVImport Class Initialized");
    }

    /**
     * Initialize preferences
     *
     * @param   array   $config
     * @return  void
     */
    public function initialize(array $config = array())
    {
        foreach ($config as $key => $val)
        {
            if (isset($this->$key))
            {
                $this->$key = $val;
            }
        }
    }

    /**
     * Parse a CSV file.  Returns an array of arrays (rows).
     *
     * @param   string  $filepath   Path to CSV file
     * @param   boolean $assoc      Return data as associative arrays
     * @param   boolean $detect_line_endings
     * @return  array|boolean
     */
    public function get_array($filepath = '', $assoc = TRUE, $detect_line_endings = FALSE)
    {
        // Reset class variables
        $this->column_headers = array();
        $this->filepath = '';

        // If file path is provided, set it
        if ( ! empty($filepath))
        {
            $this->filepath = $filepath;
        }

        // If line endings should be detected, set it
        if ( (bool) $detect_line_endings)
        {
            $this->detect_line_endings = TRUE;
        }

        // If no filepath was set, return FALSE
        if (empty($this->filepath))
        {
            log_message('error', 'CSVImport: No filepath was set.');
            return FALSE;
        }

        // If the file doesn't exist, return FALSE
        if ( ! file_exists($this->filepath))
        {
            log_message('error', 'CSVImport: File does not exist at ' . $this->filepath);
            return FALSE;
        }

        // Open the CSV file
        if ( ! ($fp = fopen($this->filepath, 'r')))
        {
            log_message('error', 'CSVImport: Could not open the file at ' . $this->filepath);
            return FALSE;
        }

        // If we need to detect line endings, set the INI setting
        if ($this->detect_line_endings === TRUE)
        {
            ini_set('auto_detect_line_endings', TRUE);
        }

        // Get the column headers
        $this->column_headers = fgetcsv($fp, 0, $this->delimiter);

        // If we are removing the header row, do it
        if ($this->remove_header_row === TRUE)
        {
            // Don't do anything, we already got the headers
        }

        $num_columns = count($this->column_headers);

        $data = array();

        // Loop through each row
        while ( ($row = fgetcsv($fp, 0, $this->delimiter)) !== FALSE)
        {
            // If the row is empty, skip it
            if (empty($row))
            {
                continue;
            }

            // If the row has a different number of columns than the header, skip it
            if (count($row) !== $num_columns)
            {
                log_message('error', 'CSVImport: Row has a different number of columns than the header. Skipping row.');
                continue;
            }

            if ($assoc === TRUE)
            {
                // Combine the headers with the row data
                $data[] = array_combine($this->column_headers, $row);
            }
            else
            {
                $data[] = $row;
            }
        }

        // Close the file
        fclose($fp);

        return $data;
    }

    /**
     * Get the column headers of the CSV file.
     *
     * @return  array
     */
    public function get_column_headers()
    {
        return $this->column_headers;
    }

    /**
     * Set the delimiter character.
     *
     * @param   string  $delimiter
     * @return  void
     */
    public function set_delimiter($delimiter = ',')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Set whether to detect line endings.
     *
     * @param   boolean $detect_line_endings
     * @return  void
     */
    public function set_detect_line_endings($detect_line_endings = FALSE)
    {
        $this->detect_line_endings = (bool) $detect_line_endings;
    }

    /**
     * Set whether to remove the header row.
     *
     * @param   boolean $remove_header_row
     * @return  void
     */
    public function set_remove_header_row($remove_header_row = TRUE)
    {
        $this->remove_header_row = (bool) $remove_header_row;
    }

}
