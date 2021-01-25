<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2016  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\TI\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta, inclusão, edição e exclusão
 *
 * @author Ramon Barros
 * @package Lidere\Modules\TI
 * @category Models
 */
class Ticket
{

    protected $core;

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->table = 'ticket';
    }

    public function getTickets($company_id = false, $user_id = false, $project_id = false, $search = false)
    {
        $sql = "SELECT DISTINCT t.id, t.title, t.created_at, t.billed,  t.category, c.name company_name, tp.name type_name, pr.id priority_id, pr.name priority_name, s.id status_id, s.name status_name, t.punctuation punctuation
				  FROM ticket t
				  LEFT JOIN ticket_called tc 	ON tc.ticket_id = t.id
				  LEFT JOIN project p 			ON p.id = t.project_id
				  LEFT JOIN company c 			ON c.id = p.company_id
				  LEFT JOIN type tp 			ON tp.id = t.type_id
				  LEFT JOIN priority pr 		ON pr.id = t.priority_id
				  LEFT JOIN ticket_status ts 	ON ts.ticket_id = t.id
				  LEFT JOIN status s 			ON s.id = ts.status_id
				 WHERE ts.created_at = (SELECT MAX(created_at) FROM ticket_status WHERE ticket_id = t.id)";

        if ($search) {
            foreach ($search as $column => $value) {
                foreach ($value as $col => $val) {
                    if (is_array($val) && $col == 'date') {
                        $sql .= " AND t.id IN (SELECT ticket_id FROM ticket_called WHERE date BETWEEN '".$val[0]."' AND '".$val[1]."')";
                        break;
                    } else {
                        $sql .= " AND " . $col .  " '".$val."'";
                    }
                }
            }
        }

        if ($company_id) {
            $sql .= " AND c.id = :query";
            $string = $company_id;
        } elseif ($user_id) {
            $sql .= " AND t.user_id = :query";
            $string = $user_id;
        } elseif ($project_id) {
            $sql .= " AND t.project_id = :query";
            $string = $project_id;
        }

        $sql .= " ORDER BY t.punctuation DESC, t.created_at, t.id ASC";

        $query = $this->core->db->prepare($sql);

        if ($company_id || $user_id || $project_id) {
            $query->bindValue(':query', $string);
        }

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;
    }

    public function getTicketsForBilling($search = false)
    {
        $sql = "SELECT DISTINCT t.id, t.title, t.created_at, t.financial_details, t.billed, c.id company_id, c.name company_name, tp.name type_name, pr.id priority_id, pr.name, s.id status_id, s.name status_name, c.tax_percentage company_tax_percentage
				  FROM ticket t, ticket_called tc, project p, company c, type tp, priority pr, ticket_status ts, status s
				 WHERE s.id 		= ts.status_id
				   AND ts.ticket_id = t.id
				   AND pr.id 		= t.priority_id
				   AND tp.id 		= t.type_id
				   AND c.id 		= p.company_id
				   AND p.id 		= t.project_id
				   AND tc.ticket_id = t.id
				   AND t.type_id 	<> 4
				   AND tc.type 		= 'NORMAL'
				   AND ts.created_at = (SELECT MAX(created_at) FROM ticket_status WHERE ticket_id = t.id)";

        if ($search) {
            foreach ($search as $column => $value) {
                $sql .= " AND " . $column . " ". $value;
            }
        }

        $sql .= " ORDER BY c.name, t.id ASC";
        $query = $this->core->db->prepare($sql);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;
    }

    public function getTicketStatus($id)
    {
        $sql = "SELECT ts.description, DATE_FORMAT(ts.created_at, '%d/%m/%Y %H:%i') created_at_br, s.id, s.name FROM ticket_status ts, status s WHERE s.id = ts.status_id AND ts.ticket_id = :id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getTicketStatusById($id)
    {
        $sql = "SELECT ts.description, DATE_FORMAT(ts.created_at, '%d/%m/%Y %H:%i') created_at_br, s.id, s.name FROM ticket_status ts, status s WHERE s.id = ts.status_id AND ts.id = :id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getTicketCalled($id)
    {
        $sql = "SELECT *, DATE_FORMAT(`date`, '%d/%m/%Y') `date`, DATE_FORMAT(`hour_start`, '%H:%i') hour_start, DATE_FORMAT(`hour_finish`, '%H:%i') hour_finish, TIMEDIFF(`hour_finish`, `hour_start`) hour_total FROM ticket_called WHERE id = :id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getCalledsTicket($ticket_id, $date = false, $user = false, $order = 'DESC', $page_from = 'ticket', $search_billing = false)
    {
        $where = "";
        if ($page_from == 'billing') {
            if ($search_billing) {
                foreach ($search_billing as $column => $value) {
                    foreach ($value as $col => $val) {
                        if (is_array($val) && $col == 'date') {
                            $where .= " AND t.id IN (SELECT ticket_id FROM ticket_called WHERE date BETWEEN '".$val[0]."' AND '".$val[1]."' AND type = 'NORMAL')";
                            break;
                        } else {
                            $where .= " AND " . $col .  " '".$val."'";
                        }
                    }
                }
            }
        }

        $where2 = "";
        if ($page_from == 'billing') {
            if ($search_billing) {
                foreach ($search_billing as $column => $value) {
                    foreach ($value as $col => $val) {
                        if (is_array($val) && $col == 'date') {
                            $where2 .= " AND tc.date BETWEEN '".$val[0]."' AND '".$val[1]."'";
                            break;
                        } else {
                            $where2 .= " AND " . $col .  " '".$val."'";
                        }
                    }
                }
            }
        }

        if ($date) {
            if (is_array($date)) {
                null;
            } else {
                $where .= " AND tc.date = '".$date."'";
            }
        }

        if ($user) {
            $where .= " AND tc.user_id = '".$user."'";
        }

        $sql = "SELECT DISTINCT tc.*, DATE_FORMAT(`tc`.`date`, '%d/%m/%Y') `date`, DATE_FORMAT(`tc`.`hour_start`, '%H:%i') hour_start, DATE_FORMAT(`tc`.`hour_finish`, '%H:%i') hour_finish, TIMEDIFF(`tc`.`hour_finish`, `tc`.`hour_start`) hour_total, FORMAT(((TIME_TO_SEC(`tc`.`hour_finish`)-TIME_TO_SEC(`tc`.`hour_start`))/60),0) minutes, u.name user_name, h.billing, h.name hour_name, h.goal FROM ticket_called tc, user u, hour h, ticket t, company c, project p WHERE p.id = t.project_id AND p.company_id = c.id AND t.id = tc.ticket_id AND h.id = tc.hour_id AND u.id = tc.user_id AND tc.ticket_id = :ticket_id AND tc.type = 'NORMAL' ".$where." ".$where2." ORDER BY `tc`.`date` ASC";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':ticket_id', $ticket_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getCalledsByCompanyId($company_id, $limit = 99)
    {
        $sql = "SELECT *, DATE_FORMAT(`tc`.`date`, '%d/%m/%Y') `date`, DATE_FORMAT(`tc`.`hour_start`, '%H:%i') hour_start, DATE_FORMAT(`tc`.`hour_finish`, '%H:%i') hour_finish, TIMEDIFF(`tc`.`hour_finish`, `tc`.`hour_start`) hour_total, FORMAT(((TIME_TO_SEC(`tc`.`hour_finish`)-TIME_TO_SEC(`tc`.`hour_start`))/60),0) minutes, u.name user_name, h.name hour_name" .
               "  FROM ticket_called tc, ticket t, project p, user u, hour h" .
               " WHERE h.id = tc.hour_id" .
               "   AND u.id = tc.user_id" .
               "   AND p.id = t.project_id" .
               "   AND t.id = tc.ticket_id" .
               "   AND tc.type = 'NORMAL'" .
               "   AND p.company_id = :company_id" .
               " ORDER BY `tc`.`date` DESC" .
               " LIMIT ".$limit;
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':company_id', $company_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;
    }

    public function getAccumulatedValuePerCompanyWithIndex($initialDate = false, $finalDate = false, $companyId = false, $onlyCompanyWithBilling = false) {
        $sql = 'select (@row_number := @row_number + 1) ranking, nome, total from (
                  select company.name nome, company.id,
                    ROUND(
                        SUM(
                          CASE closed_value
                          WHEN "OFF"
                            THEN ((TIME_TO_SEC(TIMEDIFF(hour_finish, hour_start)) / 60) * hour_value) / 60
                          WHEN "ON"
                            THEN hour_value
                          END
                      ), 2) total
                  FROM ticket_called
                    inner join ticket on ticket_called.ticket_id = ticket.id
                    inner join project on ticket.project_id = project.id
                    inner join company on project.company_id = company.id
                    inner join hour on ticket_called.hour_id = hour.id
                  WHERE hour.billing = "ON"
                    AND ticket_called.type = "NORMAL"';

        if ($initialDate) {
            $sql .= " AND ticket_called.date >= \"$initialDate\"";
        }

        if ($finalDate) {
            $sql .= " AND ticket_called.date <= \"$finalDate\"";
        }

        if ($companyId) {
            $sql .= " AND company.id = $companyId";
        }

        $sql .= " GROUP BY company.id
                  ORDER BY total DESC
                ) data";

        if ($onlyCompanyWithBilling) {
            $sql .= " WHERE total > 0";
        }

        $query = $this->core->db->prepare($sql);

        $queryRowNumber = $this->core->db->prepare("set @row_number = 0;");
        $queryRowNumber->execute();

        $data = null;

        if ($query->execute()) {
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function getSumOfAccumulatedValuePerCompanyWithIndex($initialDate = false, $finalDate = false, $companyId = false, $onlyCompanyWithBilling = false) {
        $sql = 'select ROUND(
                            SUM(
                              CASE closed_value
                              WHEN "OFF"
                                THEN ((TIME_TO_SEC(TIMEDIFF(hour_finish, hour_start)) / 60) * hour_value) / 60
                              WHEN "ON"
                                THEN hour_value
                              END
                          ), 2) total
              FROM ticket_called
                inner join ticket on ticket_called.ticket_id = ticket.id
                inner join project on ticket.project_id = project.id
                inner join company on project.company_id = company.id
                inner join hour on ticket_called.hour_id = hour.id
              WHERE hour.billing = "ON"
                AND ticket_called.type = "NORMAL"';

        if ($initialDate) {
            $sql .= " AND ticket_called.date >= \"$initialDate\"";
        }

        if ($finalDate) {
            $sql .= " AND ticket_called.date <= \"$finalDate\"";
        }

        if ($companyId) {
            $sql .= " AND company.id = $companyId";
        }

        $query = $this->core->db->prepare($sql);

        $data = null;
        if ($query->execute()) {
            $data = $query->fetch(PDO::FETCH_ASSOC);
        }

        return !empty($data['total']) ? $data['total'] : 0;
    }

    public function getTicketById($id)
    {
        $sql = "SELECT t.*, DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') created_at_br, c.id company_id, c.name company_name, u.name user_name, tp.name type_name, pr.name priority_name, c.tax_percentage company_tax_percentage" .
               "  FROM ".$this->table." t, project p, company c, user u, type tp, priority pr" .
               " WHERE pr.id = t.priority_id" .
               "   AND tp.id = t.type_id" .
               "   AND u.id = t.user_id" .
               "   AND c.id = p.company_id" .
               "   AND p.id = t.project_id" .
               "   AND t.id = :id";

        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }

        return $r;
    }

    public function getLastCalled($limit = 99)
    {
        $sql = "SELECT tc.*, DATE_FORMAT(`tc`.`date`, '%d/%m/%Y') `date`, DATE_FORMAT(`tc`.`hour_start`, '%H:%i') hour_start, DATE_FORMAT(`tc`.`hour_finish`, '%H:%i') hour_finish, TIMEDIFF(`tc`.`hour_finish`, `tc`.`hour_start`) hour_total, u.name user_name, t.title, h.name hour_name " .
               "  FROM ticket_called tc, user u, ticket t, hour h " .
               " WHERE h.id = tc.hour_id " .
               "   AND t.id = tc.ticket_id " .
               "   AND u.id = tc.user_id " .
               "   AND tc.type = 'NORMAL' " .
               " ORDER BY `tc`.`date` DESC LIMIT ".$limit;
        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;
    }

    public function getTicketsTypesWithCount($company_id = false)
    {
        $where = null;
        if ($company_id) {
            $where .= " AND p.company_id = ".$company_id;
        }

        $sql = "SELECT tp.name, COUNT(t.type_id) qty, tp.color color" .
               "  FROM ticket t, type tp, project p" .
               " WHERE p.id = t.project_id" .
               "   AND tp.id = t.type_id" .
               $where .
               " GROUP BY t.type_id";
        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;
    }

    public function getCountTicketsByLastStatus($company_id = false)
    {
        $where = $company_id ? " AND p.company_id = ".$company_id : "";

        $sql = "SELECT s.name, COUNT(s.id) qty, s.color color
				  FROM ticket t, ticket_status ts, status s, project p
				 WHERE p.id = t.project_id
				   AND s.id = ts.status_id
				   AND ts.created_at = (SELECT MAX(created_at) FROM ticket_status WHERE ticket_id = t.id)
				   AND ts.ticket_id = t.id ".$where."
				 GROUP BY s.name, s.color
				 ORDER BY ts.created_at DESC";

        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;
    }

    public function getTicketsByExpenseId($expense_id)
    {
        $sql = "SELECT * FROM ticket_expense WHERE expense_id = :expense_id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':expense_id', $expense_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getCountTicketsByPriority()
    {
        $sql = "SELECT p.name, COUNT(t.priority_id) qty FROM ticket t, priority p WHERE p.id = t.priority_id GROUP BY priority_id";
        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;
    }

    public function getCalledsByHourId($hour_id)
    {
        $sql = "SELECT * FROM ticket_called WHERE type = 'NORMAL' AND hour_id = :hour_id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':hour_id', $hour_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;
    }

    public function getTotalHoursCalleds($ticket_id, $called_ids = false, $billing = false, $page_from = 'ticket' , $rel = false)
    {
        $from = '';
        $where = '';
        if ($page_from == 'billing') {
            $where .= ' AND tc.status = \'CONCLUIDO\' ';
        }

        if ($billing) {
            $where .= ' AND h.billing = "ON"';
        }
        if ($rel){
            $where .= ' AND tc.add_to_billing_report = "ON"';
        }

        if ($called_ids) {
            $where .= ' AND tc.id in ('.join(',', $called_ids).')';
        }

        $sql = "SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(tc.hour_finish) - TIME_TO_SEC(tc.hour_start))) hours
				  FROM ticket_called tc , hour h
				 WHERE tc.type = 'NORMAL'
                   AND h.id = tc.hour_id
				   AND tc.ticket_id = :ticket_id".$where;
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':ticket_id', $ticket_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }

        return $r['hours'];
    }

    public function getTicketCalledsById($ticket_id, $called_ids, $order = 'DESC')
    {
        $where = 'AND tc.id in ('.join(',', $called_ids).')';

        $sql = "SELECT tc.*, DATE_FORMAT(`tc`.`date`, '%d/%m/%Y') `date`, DATE_FORMAT(`tc`.`hour_start`, '%H:%i') hour_start, DATE_FORMAT(`tc`.`hour_finish`, '%H:%i') hour_finish, TIMEDIFF(`tc`.`hour_finish`, `tc`.`hour_start`) hour_total, FORMAT(((TIME_TO_SEC(`tc`.`hour_finish`)-TIME_TO_SEC(`tc`.`hour_start`))/60),0) minutes, u.name user_name, h.name hour_name FROM ticket_called tc, user u, hour h WHERE h.id = tc.hour_id AND u.id = tc.user_id AND tc.type = 'NORMAL' AND tc.ticket_id = :ticket_id ".$where." ORDER BY `tc`.`date` DESC";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':ticket_id', $ticket_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;
    }

    public function getExpensesTypes()
    {
        $sql = "SELECT id, name, calc_qty FROM expense WHERE status = :status ORDER BY name";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':status', 'ON');

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $r = 0;
        }
        return $r;
    }

    public function getExpensesTicket($ticket_id, $user_id = false, $company_billing = 'ON')
    {
        $where = $company_billing != 'ALL' ? " AND te.company_billing = '".$company_billing."'" : "";

        $sql = "SELECT te.*, DATE_FORMAT(te.date, '%d/%m/%Y') date_at, e.name type, u.name user FROM ticket_expense te, expense e, user u WHERE u.id = te.user_id AND e.id = te.expense_id AND te.ticket_id = :ticket_id " . $where;
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':ticket_id', $ticket_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getExpensesTicketNotPay($ticket_id, $user_id = false, $company_billing = 'ON')
    {
        $where = $company_billing != 'ALL' ? " AND te.company_billing = '".$company_billing."'" : "";

        $sql = "SELECT te.*, DATE_FORMAT(te.date, '%d/%m/%Y') date_at, e.name type, u.name user
                  FROM ticket_expense te, expense e, user u
                WHERE u.id = te.user_id
                  AND e.id = te.expense_id
                  AND te.paid_at IS NULL
                  AND te.ticket_id = :ticket_id " . $where;
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':ticket_id', $ticket_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getTicketExpenseById($id)
    {
        $sql = "SELECT * FROM ticket_expense WHERE id = :id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getFilesTicket($ticket_id, $called_id = false)
    {
        $where = $called_id ? " AND called_id = ".$called_id : "";

        $sql = "SELECT id, description, name, type, size, content, DATE_FORMAT(created_at,'%d/%m/%Y %H:%i') created_at_br, ticket_id, ticket_called_id, user_id FROM file WHERE ticket_id = :ticket_id " . $where;
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':ticket_id', $ticket_id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getFileById($id)
    {
        $sql = "SELECT * FROM file WHERE id = :id";
        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getCalleds($search = false)
    {
        if (!$search) {
            return false;
        }

        $sql = "SELECT tc.*, DATE_FORMAT(`tc`.`date`, '%d/%m/%Y') `date`, DATE_FORMAT(`tc`.`hour_start`, '%H:%i') hour_start, DATE_FORMAT(`tc`.`hour_finish`, '%H:%i') hour_finish, TIMEDIFF(`tc`.`hour_finish`, `tc`.`hour_start`) hour_total, FORMAT(((TIME_TO_SEC(`tc`.`hour_finish`)-TIME_TO_SEC(`tc`.`hour_start`))/60),0) minutes, u.name user_name, h.billing, h.name hour_name, h.goal  FROM ticket_called tc, user u, hour h WHERE h.id = tc.hour_id AND u.id = tc.user_id AND tc.type = 'NORMAL' ";
        $where = "";
        if ($search) {
            foreach ($search as $coluna => $valor) {
                $where .= ' AND ' . $coluna . " " . $valor;
            }
        }
        $sql .= $where;
        $sql .= " ORDER BY `tc`.`date` ASC";

        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getCalledsTicketBilling($search = false)
    {
        $sql = "SELECT tc.*, TIMEDIFF(tc.hour_finish, tc.hour_start) hour_total, FORMAT(((TIME_TO_SEC(tc.hour_finish)-TIME_TO_SEC(tc.hour_start))/60),0) minutes, u.name user_name, h.billing, h.name hour_name, tc.status
				  FROM ticket_called tc, user u, hour h
				 WHERE h.id = tc.hour_id
				   AND u.id = tc.user_id
                   AND tc.add_to_billing_report = 'ON'
				   AND tc.type = 'NORMAL'";

        if ($search) {
            foreach ($search as $column => $value) {
                $sql .= " AND " . $column . " ". $value;
            }
        }

        $sql .= " ORDER BY tc.ticket_id, tc.date ASC";
        $query = $this->core->db->prepare($sql);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;
    }


    public function getCalledsByAttendance($user_id , $search = false)
    {
        if (!$search) {
            return false;
        }

        $sql = "SELECT distinct tc.*, `tc`.`date`,
                       DATE_FORMAT(`tc`.`hour_start`, '%H:%i') hour_start,
                       DATE_FORMAT(`tc`.`hour_finish`, '%H:%i') hour_finish,
                       TIMEDIFF(`tc`.`hour_finish`, `tc`.`hour_start`) hour_total,
                       FORMAT(((TIME_TO_SEC(`tc`.`hour_finish`)-TIME_TO_SEC(`tc`.`hour_start`))/60),0) minutes,
                       u.name user_name,
                       h.billing,
                       h.name hour_name,
                       h.goal,
                       c.name company_name,
                       t.id ticket_id,
                       h.billing hour_billing
                FROM ticket_called tc, user u, hour h, ticket t, project p, company c
               WHERE h.id = tc.hour_id
                 AND u.id = tc.user_id
                 AND t.id = tc.ticket_id
                 AND t.project_id = p.id
                 AND p.company_id = c.id
                 AND tc.user_id = {$user_id}
                 AND tc.type = 'NORMAL' ";

        if ($search) {
            foreach ($search as $column => $value) {
                foreach ($value as $col => $val) {
                    if (is_array($val) && $col == 'date') {
                        $sql .= " AND tc.date BETWEEN '".$val[0]."' AND '".$val[1]."'";
                        break;
                    } else {
                        $sql .= " AND " . $col .  " '".$val."'";
                    }
                }
            }
        }
        $sql .= " ORDER BY `tc`.`date` ASC";

      //  echo '<pre>';
      //  var_export($sql);die;

        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getTotalHoursCalledsByAttendance($user_id, $search = false)
    {

        $sql = "SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(tc.hour_finish) - TIME_TO_SEC(tc.hour_start))) hours
                  FROM ticket_called tc , user u, hour h, ticket t, project p, company c
                 WHERE h.id = tc.hour_id
                   AND u.id = tc.user_id
                   AND t.id = tc.ticket_id
                   AND t.project_id = p.id
                   AND p.company_id = c.id
                   AND tc.user_id = {$user_id}
                   AND tc.type = 'NORMAL' ";

        if ($search) {
            foreach ($search as $column => $value) {
                foreach ($value as $col => $val) {
                    if (is_array($val) && $col == 'date') {
                        $sql .= " AND tc.date BETWEEN '".$val[0]."' AND '".$val[1]."'";
                        break;
                    } else {
                        $sql .= " AND " . $col .  " '".$val."'";
                    }
                }
            }
        }

        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r['hours'];
    }


    public function getTicketsPanel($company_id = false, $user_id = false, $project_id = false, $search = false)
    {
        $sql = " SELECT DISTINCT t.id, t.title, t.created_at, t.billed,  t.category, c.name company_name, tp.name type_name, pr.id priority_id, pr.name priority_name, s.id status_id, s.name status_name, u.name user_name, t.punctuation punctuation
                  FROM ticket t
                  LEFT JOIN ticket_called tc    ON tc.ticket_id = t.id
                  LEFT JOIN project p           ON p.id = t.project_id
                  LEFT JOIN company c           ON c.id = p.company_id
                  LEFT JOIN type tp             ON tp.id = t.type_id
                  LEFT JOIN priority pr         ON pr.id = t.priority_id
                  LEFT JOIN ticket_status ts    ON ts.ticket_id = t.id
                  LEFT JOIN status s            ON s.id = ts.status_id
                  LEFT JOIN user u              ON u.id = t.user_internal_id
                 WHERE ts.created_at = (SELECT MAX(created_at) FROM ticket_status WHERE ticket_id = t.id)";

        if ($search) {
            foreach ($search as $column => $value) {
                foreach ($value as $col => $val) {
                    if (is_array($val) && $col == 'date') {
                        $sql .= " AND t.id IN (SELECT ticket_id FROM ticket_called WHERE date BETWEEN '".$val[0]."' AND '".$val[1]."')";
                        break;
                    } else {
                        $sql .= " AND " . $col .  " '".$val."'";
                    }
                }
            }
        }

        if ($company_id) {
            $sql .= " AND c.id = :query";
            $string = $company_id;
        } elseif ($user_id) {
            $sql .= " AND t.user_id = :query";
            $string = $user_id;
        } elseif ($project_id) {
            $sql .= " AND t.project_id = :query";
            $string = $project_id;
        }


        $sql .= " ORDER BY -t.punctuation DESC, t.id";

        $query = $this->core->db->prepare($sql);

        if ($company_id || $user_id || $project_id) {
            $query->bindValue(':query', $string);
        }

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;
    }


    public function getTicketByIdForm($id)
    {
        $sql = "SELECT t.*, DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') created_at_br, c.id company_id, c.name company_name, u.name user_name, tp.name type_name, pr.name priority_name" .
               "  FROM ".$this->table." t, project p, company c, user u, type tp, priority pr" .
               " WHERE pr.id = t.priority_id" .
               "   AND tp.id = t.type_id" .
               "   AND u.id = t.user_id" .
               "   AND c.id = p.company_id" .
               "   AND t.category = 'S'".
               "   AND p.id = t.project_id" .
               "   AND t.id = :id";

        $query = $this->core->db->prepare($sql);
        $query->bindValue(':id', $id);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }

        return $r;
    }


    public function getTicketByIdDevelopmentProject($id)
    {
        $sql = "SELECT t.*, DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') created_at_br, c.id company_id, c.name company_name, u.name user_name, tp.name type_name, pr.name priority_name" .
               "  FROM ".$this->table." t, project p, company c, user u, type tp, priority pr" .
               " WHERE pr.id = t.priority_id" .
               "   AND tp.id = t.type_id" .
               "   AND u.id = t.user_id" .
               "   AND c.id = p.company_id" .
               "   AND p.id = t.project_id" .
               "   AND t.development_project_id = {$id}";
        $query = $this->core->db->prepare($sql);

        $r = null;
        if ($query->execute()) {
            $r = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $r;
    }


    public function getTotalHoursCalledsByProject($user_id, $project_id, $date)
    {
        /* Desconsidera hora projeto fechado */
        $sql = "SELECT SUM(TIME_TO_SEC(tc.hour_finish) - TIME_TO_SEC(tc.hour_start)) hours
                  FROM ticket_called tc , user u, hour h, ticket t, project p, company c
                 WHERE h.id = tc.hour_id
                   AND u.id = tc.user_id
                   AND t.id = tc.ticket_id
                   AND t.project_id = p.id
                   AND h.id != \"1003\"
                   AND h.goal = \"ON\"
                   AND p.company_id = c.id
                   AND tc.user_id = {$user_id}
                   AND t.development_project_id = {$project_id}
                   AND tc.type = \"NORMAL\"
                   AND tc.date BETWEEN DATE_FORMAT(\"{$date}\", \"%Y-%m-01\") AND LAST_DAY(\"{$date}\")";

        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }

        return $r['hours'];
    }

    public function getTotalHoursByProject($project_id )
    {
        /* Desconsidera hora projeto fechado */
        $sql = "SELECT IFNULL(ROUND((SUM(TIME_TO_SEC(tc.hour_finish) - TIME_TO_SEC(tc.hour_start)) / 60) /60 ,2),0) hours , d.total_hours
                  FROM ticket_called tc , user u, hour h, ticket t, project p, company c, development_project d
                 WHERE h.id = tc.hour_id
                   AND u.id = tc.user_id
                   AND t.id = tc.ticket_id
                   AND t.project_id = p.id
                   AND h.id != '1003'
                   AND d.id = t.development_project_id
                   AND p.company_id = c.id
                   AND t.development_project_id = {$project_id}
                   AND tc.type = 'NORMAL'";

        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getTotalHourByCalled($called_id)
    {
        /* Desconsidera hora projeto fechado */
        $sql = "SELECT SUM(TIME_TO_SEC(tc.hour_finish) - TIME_TO_SEC(tc.hour_start)) hours
                  FROM ticket_called tc , user u, hour h, ticket t, project p, company c
                 WHERE h.id = tc.hour_id
                   AND u.id = tc.user_id
                   AND t.id = tc.ticket_id
                   AND t.project_id = p.id
                   AND p.company_id = c.id
                   AND tc.id = {$called_id}
                   AND tc.type = 'NORMAL' ";

        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r['hours'];
    }

    public function getTotalHoursCalledsByProject2($user_id, $project_id, $developer_date)
    {
        $sql = "SELECT dev.hours_dev total_hours , (SELECT IFNULL(ROUND(((SUM(TIME_TO_SEC(tc.hour_finish) - TIME_TO_SEC(tc.hour_start)) /60) /60),2),0)
                                                      FROM ticket_called tc
                                                      WHERE tc.ticket_id = t.id
                                                        and tc.user_id = dev.user_development
                                                        and tc.date >= \"{$developer_date}\" and tc.date <= LAST_DAY(\"{$developer_date}\")) hours
                  FROM ticket t, development_project d , development_project_dev dev
                 WHERE dev.development_project_id = d.id
                   AND d.id = t.development_project_id
                   AND t.development_project_id = {$project_id}
                   AND dev.user_development     = {$user_id}
                   AND dev.date                 = \"{$developer_date}\" ";


        $query = $this->core->db->prepare($sql);

        $r = array();
        if ($query->execute()) {
            $r = $query->fetch(PDO::FETCH_ASSOC);
        }
        return $r;
    }

    public function getDateCalledById($calledId) {
        $sql = "select date from ticket_called where id = :calledId";

        $query = $this->core->db->prepare($sql);

        $query->bindValue(":calledId", $calledId);

        return $query->execute() ? $query->fetch(PDO::FETCH_ASSOC)['date'] : null;
    }

}
