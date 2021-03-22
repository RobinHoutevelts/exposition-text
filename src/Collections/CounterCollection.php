<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Collections;

use Iterator;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Metrics\Counter;
use function count;

final class CounterCollection extends AbstractMetricCollection
{
	/** @var array|Counter[] */
	private $counters = [];

	public static function withMetricName( NamesMetric $metricName ) : self
	{
		return new static( $metricName, 'counter' );
	}

	public static function fromCounters( NamesMetric $metricName, Counter $counter, Counter ...$counters ) : self
	{
		$collection = self::withMetricName( $metricName );
		$collection->add( $counter, ...$counters );

		return $collection;
	}

    public static function merge( NamesMetric $metricName, self $counterCollection, self ...$counterCollections ) : self
    {
        $collection = self::withMetricName( $metricName );

        array_unshift($counterCollections, $counterCollection);
        unset($counterCollection);
        foreach ($counterCollections as $counterCollection) {
            $collection->add(...$counterCollection->counters);
        }

        return $collection;
    }

	public function add( Counter $counter, Counter ...$counters ) : void
	{
		$this->counters[] = $counter;
		if ( [] !== $counters )
		{
			$this->counters = array_merge( $this->counters, $counters );
		}
	}

	public function count() : int
	{
		return count( $this->counters );
	}

	public function getMetricLines() : Iterator
	{
		if ( 0 === $this->count() )
		{
			return;
		}

		yield $this->getTypeString();

		$helpString = $this->getHelpString();
		if ( '' !== $helpString )
		{
			yield $helpString;
		}

		foreach ( $this->counters as $counter )
		{
			yield $this->getMetricName()->toString() . $counter->getSampleString();
		}
	}

	public function getMetricsString() : string
	{
		return implode( "\n", iterator_to_array( $this->getMetricLines(), false ) );
	}
}
