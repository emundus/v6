<?php
namespace Falang\Database;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseIterator;
use Joomla\Database\FetchMode;
use Joomla\Database\StatementInterface;
use Joomla\CMS\Component\ComponentHelper;

require_once(JPATH_SITE . '/components/com_falang/helpers/falang.class.php');


class FDatabaseIterator extends DatabaseIterator implements \Countable, \Iterator
{

    /**
     * The class of object to create.
     *
     * @var    string
     * @since  1.0
     */
    protected $class;

    /**
     * The name of the column to use for the key of the database record.
     *
     * @var    mixed
     * @since  1.0
     */
    private $column;
    /**
     * The current database record.
     *
     * @var    mixed
     * @since  1.0
     */
    private $current;

    /**
     * A numeric or string key for the current database record.
     *
     * @var    scalar
     * @since  1.0
     */
    private $key;

    /**
     * The number of fetched records.
     *
     * @var    integer
     * @since  1.0
     */
    private $fetched = 0;


    /**
     * array use to store the original data
     *
     * @var    array
     * @since  1.0
     */
    private $result;


    private $reftable;


    private $current_language_tag;

    private $default_language_tag;

    /**
     * Database iterator constructor.
     *
     * @param StatementInterface $statement The statement holding the result set to iterate.
     * @param string $column An option column to use as the iterator key.
     * @param string $class The class of object that is returned.
     *
     * @throws  \InvalidArgumentException
     * @since   1.0
     */
    public function __construct(StatementInterface $statement, $column = null, $class = \stdClass::class)
    {
        if (!class_exists($class))
        {
            throw new \InvalidArgumentException(sprintf('new %s(*%s*, cursor)', \get_class($this), \gettype($class)));
        }

        if ($statement)
        {
            $fetchMode = $class === \stdClass::class ? FetchMode::STANDARD_OBJECT : FetchMode::CUSTOM_OBJECT;

            // PDO doesn't allow extra arguments for \PDO::FETCH_CLASS, so only forward the class for the custom object mode
            if ($fetchMode === FetchMode::STANDARD_OBJECT)
            {
                $statement->setFetchMode($fetchMode);
            }
            else
            {
                $statement->setFetchMode($fetchMode, $class);
            }
        }

        $this->statement = $statement;
        $this->class     = $class;
        $this->column    = $column;
        $this->fetched   = 0;

        $i = 0;
        $row_count = $this->statement->rowCount();
        for ($i = 0; $i < $row_count; $i++) {
            $this->getElement();
            $this->result[$i] = $this->current;
        }
        $this->key = 0;
        $this->fetched = 1;

        $this->reftable = \JFactory::getDbo()->getRefTables();
        $this->current_language_tag = Factory::getLanguage()->getTag();
        $this->default_language_tag = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

    }

    /**
     * Moves forward to the next result from the SQL query.
     * and store it in the cached result
     *
     * @return  void
     *
     * @since   1.0
     */
    public function getElement()
    {
        // Set the default key as being the number of fetched object
        $this->key = $this->fetched;

        // Try to get an object
        $this->current = $this->fetchObject();

        // If an object has been found
        if ($this->current)
        {
            // Set the key as being the indexed column (if it exists)
            if ($this->column && isset($this->current->{$this->column}))
            {
                $this->key = $this->current->{$this->column};
            }

            // Update the number of fetched object
            $this->fetched++;
        }
    }
    /**
     * move the key only to simulate the normal iterator
     *
     * @return  void
     *
     * @since   1.0
     */
    public function next()
    {
        $this->key = $this->fetched;
        $this->fetched++;
    }


    public function current() {
        $current = $this->result[$this->key];
        //get translated content for non default language
        if ($this->current_language_tag != $this->default_language_tag){
            //rows supposed to be an array
            $rows = array(0 => $current);
            \Falang::translateList($rows, $this->current_language_tag, $this->reftable);
        }
        $this->key = $this->key + 1;
        return $current;
    }

    /**
     * Checks if the current position of the iterator is valid.
     *
     * @return  boolean
     *
     * @see     Iterator::valid()
     * @since   1.0
     */
    public function valid()
    {
        return isset($this->result[$this->key]);
    }


}