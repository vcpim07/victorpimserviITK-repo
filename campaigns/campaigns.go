package campaigns

import (
	"context"
	"errors"
	"fmt"
	"time"

	"gorm.io/gorm"
)

//
// =======================================
// MODELO BASE
// =======================================
//

type Campaign struct {
	ID               string `gorm:"type:char(36);primaryKey"`
	ProductID        string `gorm:"type:char(36);not null"`
	Name             string `gorm:"type:varchar(255);not null"`
	CRMID            string `gorm:"type:varchar(100)"`
	Status           bool   `gorm:"not null"`
	AtnStart         string `gorm:"type:varchar(20)"`
	AtnEnd           string `gorm:"type:varchar(20)"`
	AtnDays          string `gorm:"type:varchar(50)"`
	Notify           string `gorm:"type:varchar(50)"`
	Quality          bool
	Role             string `gorm:"type:varchar(50)"`
	Shift            string `gorm:"type:varchar(50)"`
	Manual           bool
	Duplicates       bool
	DupDays          int
	MailTemplate     string `gorm:"type:varchar(255)"`
	CRMConn          string `gorm:"type:varchar(255)"`
	WabBot           string `gorm:"type:varchar(255)"`
	WabMessages      string `gorm:"type:text"`
	GradeOptions     string `gorm:"type:varchar(255)"`
	ChannelCRMID     string `gorm:"type:varchar(100)"`
	IScore           int
	AddressPosition  string `gorm:"type:varchar(255)"`
	Latitude         string `gorm:"type:varchar(100)"`
	Longitude        string `gorm:"type:varchar(100)"`
	LocationName     string `gorm:"type:varchar(255)"`
	AutoAssignLeadIA bool
	AutoAssignTimeIA int
	UpdatedAt        time.Time
}

//
// =======================================
// REPOSITORY LAYER (Capa de acceso a datos)
// =======================================
//

// Define los métodos que el repositorio debe implementar
type Repository interface {
	Update(campaign *Campaign) error
	GetByID(productID, campaignID string) (*Campaign, error)
}

// Implementación concreta usando GORM
type campaignRepository struct {
	db *gorm.DB
}

// Constructor del repositorio
func NewRepository(db *gorm.DB) Repository {
	return &campaignRepository{db: db}
}

// Actualiza una campaña en la base de datos
func (r *campaignRepository) Update(campaign *Campaign) error {
	return r.db.Model(&Campaign{}).
		Where("id = ? AND product_id = ?", campaign.ID, campaign.ProductID).
		Updates(campaign).Error
}

// Obtiene una campaña por ID
func (r *campaignRepository) GetByID(productID, campaignID string) (*Campaign, error) {
	var c Campaign
	err := r.db.Where("product_id = ? AND id = ?", productID, campaignID).First(&c).Error
	return &c, err
}

//
// =======================================
// SERVICE LAYER (Capa de lógica de negocio)
// =======================================
//

// Define la interfaz del servicio
type Service interface {
	UpdateCampaign(ctx context.Context, req UpdateCampaignRequest) error
}

// Implementación concreta del servicio
type service struct {
	repo Repository
}

// Constructor del servicio
func NewService(repo Repository) Service {
	return &service{repo: repo}
}

// Request DTO (equivalente a datos recibidos por POST)
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

// Lógica principal del servicio
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

//
// =======================================
// FUNCIONES AUXILIARES
// =======================================
//

// Convierte un slice []int en un string tipo "1,2,3"
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
