package campaigns

import "gorm.io/gorm"

type Repository interface {
	Update(campaign *Campaign) error
	GetByID(productID, campaignID string) (*Campaign, error)
}

type campaignRepository struct {
	db *gorm.DB
}

func NewRepository(db *gorm.DB) Repository {
	return &campaignRepository{db: db}
}

func (r *campaignRepository) Update(campaign *Campaign) error {
	return r.db.Model(&Campaign{}).
		Where("id = ? AND product_id = ?", campaign.ID, campaign.ProductID).
		Updates(campaign).Error
}

func (r *campaignRepository) GetByID(productID, campaignID string) (*Campaign, error) {
	var c Campaign
	err := r.db.Where("product_id = ? AND id = ?", productID, campaignID).First(&c).Error
	return &c, err
}
