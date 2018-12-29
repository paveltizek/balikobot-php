<?php

namespace Inspirum\Balikobot\Definitions;

class Request
{
    /**
     * Add a package
     *
     * @var string
     */
    public const ADD = 'add';
    
    /**
     * Drop a package
     *
     * @var string
     */
    public const DROP = 'drop';
    
    /**
     * Track a package
     *
     * @var string
     */
    public const TRACK = 'track';
    
    /**
     * Track a package; get the last brief info
     *
     * @var string
     */
    public const TRACK_STATUS = 'trackstatus';
    
    /**
     * List of packages
     *
     * @var string
     */
    public const OVERVIEW = 'overview';
    
    /**
     * Get labels
     *
     * @var string
     */
    public const LABELS = 'labels';
    
    /**
     * Get the package info
     *
     * @var string
     */
    public const PACKAGE = 'package';
    
    /**
     * Order shipment
     *
     * @var string
     */
    public const ORDER = 'order';
    
    /**
     * Get the shipment details
     *
     * @var string
     */
    public const ORDER_VIEW = 'orderview';
    
    /**
     * Get the shipment pickup details
     *
     * @var string
     */
    public const ORDER_PICKUP = 'orderpickup';
    
    /**
     * List of offered services
     *
     * @var string
     */
    public const SERVICES = 'services';
    
    /**
     * List of units for palette shipping
     *
     * @var string
     */
    public const MANIPULATION_UNITS = 'manipulationunits';
    
    /**
     * List of available branches
     *
     * @var string
     */
    public const BRANCHES = 'branches';
    
    /**
     * List of available branches with details
     *
     * @var string
     */
    public const FULL_BRANCHES = 'fullbranches';
    
    /**
     * List of available countries
     *
     * @var string
     */
    public const COUNTRIES = 'countries4service';
    
    /**
     * List of available zip codes
     *
     * @var string
     */
    public const ZIP_CODES = 'zipcodes';
    
    /**
     * Check add-package data
     *
     * @var string
     */
    public const CHECK = 'check';
    
    /**
     * List of ADR units
     *
     * @var string
     */
    public const ADR_UNITS = 'adrunits';
}