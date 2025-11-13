package campaigns

import (
	"context"
	"errors"
	"fmt"
	"time"
)

type Service interface {
	UpdateCampaign(ctx context.Context, req UpdateCampaignRequest) error
}

type service struct {
	repo Repository
}

func NewService(repo Repository) Service {
	return &service{repo: repo}
}

// UpdateCampaignRequest contiene los datos recibidos desde un formulario (equivalente a $_POST)
type UpdateCampaignRequest struct {
	ProductID        string
	CampaignID       string
	Name             string
	CRMID            string
	Status           bool
	AtnStart         string
	AtnEnd           string
	AtnDays          []int
	NotifyChannels   []int
	Quality          bool
	Role             string
	Shift            string
	Manual           bool
	Duplicates       bool
	DupDays          int
	MailTemplate     string
	CRMConn          string
	WabBot           string
	WabMessages      string
	GradeOptions     string
	ChannelCRMID     string
	IScore           int
	Address          string
	Latitude         string
	Longitude        string
	LocationName     string
	AutoAssignLeadIA bool
	AutoAssignTimeIA int
}

func (s *service) UpdateCampaign(ctx context.Context, req UpdateCampaignRequest) error {
	if req.IScore < 0 || req.IScore > 20 {
		return errors.New("IScore debe estar entre 0 y 20")
	}

	campaign := &Campaign{
		ID:               req.CampaignID,
		ProductID:        req.ProductID,
		Name:             req.Name,
		CRMID:            req.CRMID,
		Status:           req.Status,
		AtnStart:         req.AtnStart,
		AtnEnd:           req.AtnEnd,
		AtnDays:          joinInts(req.AtnDays),
		Notify:           joinInts(req.NotifyChannels),
		Quality:          req.Quality,
		Role:             req.Role,
		Shift:            req.Shift,
		Manual:           req.Manual,
		Duplicates:       req.Duplicates,
		DupDays:          req.DupDays,
		MailTemplate:     req.MailTemplate,
		CRMConn:          req.CRMConn,
		WabBot:           req.WabBot,
		WabMessages:      req.WabMessages,
		GradeOptions:     req.GradeOptions,
		ChannelCRMID:     req.ChannelCRMID,
		IScore:           req.IScore,
		AddressPosition:  req.Address,
		Latitude:         req.Latitude,
		Longitude:        req.Longitude,
		LocationName:     req.LocationName,
		AutoAssignLeadIA: req.AutoAssignLeadIA,
		AutoAssignTimeIA: req.AutoAssignTimeIA,
		UpdatedAt:        time.Now(),
	}

	return s.repo.Update(campaign)
}

func joinInts(values []int) string {
	if len(values) == 0 {
		return ""
	}
	result := ""
	for i, v := range values {
		if i > 0 {
			result += ","
		}
		result += fmt.Sprintf("%d", v)
	}
	return result
}
