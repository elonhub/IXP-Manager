<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * ContactGroup
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContactGroup extends EntityRepository
{
    /**
     * Get contact group names as an array grouped by group type.
     *
     * Returned array structure:
     *
     *     $arr = [
     *         'ROLE' => [
     *              [ 'id' => 1, 'name' => 'Billing' ],
     *              [ 'id' => 2, 'name' => 'Admin']
     *         ]
     *         'OTHER' => [
     *              [ 'id' => n, 'name' => 'Other group' ]
     *         ]
     *     ];
     *
     * @param string $type Optionally limit to a specific type
     * @param int    $cid  Contact id to filter for a particular contact
     * @return array
     */
    public function getGroupNamesTypeArray( $type = false, $cid = false )
    {
        $dql =  "SELECT cg.id AS id, cg.type AS type, cg.name AS name
             FROM \\Entities\\ContactGroup cg ";
             
        if( $cid )
            $dql .= " LEFT JOIN cg.Contacts c";
            
        $dql .= " WHERE cg.active = 1";
            
        if( $type )
            $dql .= " AND cg.type = ?1";
        
        if( $cid )
            $dql .= " AND c.id = ?2";
        
        $q = $this->getEntityManager()->createQuery( $dql );
            
        if( $type  )
           $q->setParameter( 1, $type );
        
        if( $cid )
            $q->setParameter( 2, $cid );
            
        $tmpGroups = $q->getArrayResult();

        $groups = [];
        foreach( $tmpGroups as $g )
            $groups[ $g['type'] ][ $g[ 'id' ] ] = [ 'id' => $g['id'], 'name' => $g['name'] ];

        return $groups;
    }

    /**
     * Get the number of contacts with a contact group for a given customer.
     *
     * @param \Entities\Customer $customer The customer to count the contact groups of
     * @param int $id Contact group id
     * @return int The number of contacts with a contact group for a given customer
     */
    public function countForCustomer( $customer, $id )
    {
        return $this->getEntityManager()->createQuery(
            "SELECT COUNT( cg.id )
                FROM \\Entities\\ContactGroup cg
                    LEFT JOIN cg.Contacts c
                    LEFT JOIN c.Customer cust
                WHERE
                    cg.id = ?1
                    AND cust = ?2"
            )
            ->setParameter( 1, $id )
            ->setParameter( 2, $customer )
            ->getSingleScalarResult();
    }
}
