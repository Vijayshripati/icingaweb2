<?php

namespace Icinga\Monitoring\Backend\Ido\Query;

class ServicegroupQuery extends AbstractQuery
{
    protected $columnMap = array(
        'servicegroups' => array(
            'servicegroup_name'  => 'sgo.name1',
            'servicegroup_alias' => 'sg.alias',
        ),
        'services' => array(
            'host_name'           => 'so.name1',
            'service_description' => 'so.name2'
        )
    );

    protected function joinBaseTables()
    {
        $this->baseQuery = $this->db->select()->from(
            array('sg' => $this->prefix . 'servicegroups'),
            array()
        )->join(
            array('sgo' => $this->prefix . 'objects'),
            'sg.servicegroup_object_id = sgo.' . $this->object_id
          . ' AND sgo.is_active = 1',
            array()
        );

        $this->joinedVirtualTables = array('servicegroups' => true);
    }

    protected function joinServices()
    {
        $this->baseQuery->join(
            array('sgm' => $this->prefix . 'servicegroup_members'),
            'sgm.servicegroup_id = sg.servicegroup_id',
            array()
        )->join(
            array('so' => $this->prefix . 'objects'),
            'sgm.service_object_id = so.object_id AND so.is_active = 1',
            array()
        );
    }
}
